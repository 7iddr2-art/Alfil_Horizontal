<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\TenantContextResolver;
use App\Service\TenantContextSession;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\ServerRequest;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TenantContextResolverTest extends TestCase
{
    public function testResolvesContextForActiveMembershipAndTenant(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, [
            'status' => 'active',
            'starts_at' => FrozenTime::now()->subDays(1),
            'ends_at' => FrozenTime::now()->addDays(1),
        ]);

        $context = $this->resolver()->resolve($userId, $tenantId);

        $this->assertSame($userId, $context->userId);
        $this->assertSame($tenantId, $context->tenantId);
    }

    public function testDeniesMissingOrInactiveMembership(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();

        $this->expectException(ForbiddenException::class);
        $this->resolver()->resolve($userId, $tenantId);
    }

    public function testDeniesMembershipOutsideItsEffectivePeriod(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, [
            'status' => 'active',
            'starts_at' => FrozenTime::now()->addDays(1),
        ]);

        $this->expectException(ForbiddenException::class);
        $this->resolver()->resolve($userId, $tenantId);
    }

    public function testDeniesExpiredMembership(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, [
            'status' => 'active',
            'ends_at' => FrozenTime::now()->subDays(1),
        ]);

        $this->expectException(ForbiddenException::class);
        $this->resolver()->resolve($userId, $tenantId);
    }

    public function testDeniesSuspendedTenant(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant('suspended');
        $this->createMembership($userId, $tenantId, ['status' => 'active']);

        $this->expectException(ForbiddenException::class);
        $this->resolver()->resolve($userId, $tenantId);
    }

    public function testDeniesInactiveUser(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, ['status' => 'active']);

        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->get($userId);
        $user->set('active', false);
        $users->saveOrFail($user);

        $this->expectException(ForbiddenException::class);
        $this->resolver()->resolve($userId, $tenantId);
    }

    public function testAppliesValidatedSessionContextToRequest(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, ['status' => 'active']);
        $resolver = $this->resolver();
        $context = $resolver->resolve($userId, $tenantId);
        $request = new ServerRequest();
        $session = new TenantContextSession($resolver);
        $session->select($request, $context);

        $requestWithContext = $session->apply($request, $userId);

        $resolvedContext = $requestWithContext->getAttribute('tenantContext');
        $this->assertSame($userId, $resolvedContext->userId);
        $this->assertSame($tenantId, $resolvedContext->tenantId);
    }

    public function testClearsSessionContextWhenAuthenticatedUserChanges(): void
    {
        $request = new ServerRequest();
        $resolver = $this->resolver();
        $session = new TenantContextSession($resolver);
        $request->getSession()->write('TenantContext', [
            'user_id' => 100,
            'tenant_id' => 200,
        ]);

        $requestWithoutContext = $session->apply($request, 101);

        $this->assertNull($requestWithoutContext->getAttribute('tenantContext'));
        $this->assertNull($request->getSession()->read('TenantContext'));
    }

    public function testClearsAndDeniesSessionContextAfterMembershipRevocation(): void
    {
        [$userId, $tenantId] = $this->createUserAndTenant();
        $this->createMembership($userId, $tenantId, ['status' => 'active']);
        $resolver = $this->resolver();
        $request = new ServerRequest();
        $session = new TenantContextSession($resolver);
        $session->select($request, $resolver->resolve($userId, $tenantId));

        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');
        $membership = $memberships->find()
            ->where(['user_id' => $userId, 'tenant_id' => $tenantId])
            ->firstOrFail();
        $membership->set('status', 'revoked');
        $memberships->saveOrFail($membership);

        try {
            $session->apply($request, $userId);
            $this->fail('A revoked membership must not retain its tenant context.');
        } catch (ForbiddenException) {
            $this->assertNull($request->getSession()->read('TenantContext'));
        }
    }

    /**
     * @return array{int, int}
     */
    private function createUserAndTenant(string $tenantStatus = 'active'): array
    {
        $suffix = bin2hex(random_bytes(6));
        $roles = TableRegistry::getTableLocator()->get('Roles');
        $profiles = TableRegistry::getTableLocator()->get('Profiles');
        $users = TableRegistry::getTableLocator()->get('Users');
        $tenants = TableRegistry::getTableLocator()->get('Tenants');

        $role = $roles->newEntity([
            'name' => 'tenant_context_' . $suffix,
            'is_super_admin' => false,
        ]);
        $roles->saveOrFail($role);

        $profile = $profiles->newEntity(['name' => 'tenant_context_' . $suffix]);
        $profiles->saveOrFail($profile);

        $user = $users->newEntity([
            'username' => 'tenant_context_' . $suffix,
            'password' => 'Password123',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
            'active' => true,
        ]);
        $users->saveOrFail($user);

        $tenant = $tenants->newEntity([
            'name' => 'Tenant ' . $suffix,
            'status' => $tenantStatus,
        ]);
        $tenants->saveOrFail($tenant);

        return [(int)$user->id, (int)$tenant->id];
    }

    /**
     * @param array<string, mixed> $membershipData
     */
    private function createMembership(int $userId, int $tenantId, array $membershipData): void
    {
        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');
        $membership = $memberships->newEntity($membershipData + [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
        ]);
        $memberships->saveOrFail($membership);
    }

    private function resolver(): TenantContextResolver
    {
        /** @var \App\Model\Table\TenantMembershipsTable $memberships */
        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');

        return new TenantContextResolver($memberships);
    }
}
