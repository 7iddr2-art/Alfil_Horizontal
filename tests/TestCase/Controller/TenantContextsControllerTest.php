<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class TenantContextsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testAuthenticatedUserCanSelectOnlyAnActiveMembership(): void
    {
        [$user, $tenant] = $this->createUserAndTenant();
        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');
        $membership = $memberships->newEntity([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $memberships->saveOrFail($membership);

        $this->session(['Auth' => ['id' => $user->id, 'username' => $user->username]]);
        $this->enableCsrfToken();
        $this->post('/tenant-contexts/select', ['tenant_id' => $tenant->id]);

        $this->assertRedirectContains('/tenant-contexts');
        $this->assertSession([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ], 'TenantContext');

        $this->session(['TenantContext' => [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]]);
        $this->get('/tenant-contexts');
        $this->assertResponseOk();
        $this->assertResponseContains($tenant->name);
        $this->assertResponseContains('Tenant activo');
    }

    public function testTenantSelectionRejectsMembershipFromAnotherUser(): void
    {
        [$user] = $this->createUserAndTenant();
        $tenants = TableRegistry::getTableLocator()->get('Tenants');
        $tenant = $tenants->newEntity(['name' => 'Unassigned tenant', 'status' => 'active']);
        $tenants->saveOrFail($tenant);

        $this->session(['Auth' => ['id' => $user->id, 'username' => $user->username]]);
        $this->enableCsrfToken();
        $this->post('/tenant-contexts/select', ['tenant_id' => $tenant->id]);

        $this->assertRedirectContains('/tenant-contexts');

        $this->get('/tenant-contexts');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Tenant activo');
        $this->assertResponseContains('No tiene tenants activos disponibles.');
    }

    /**
     * @return array{0: \App\Model\Entity\User, 1: \App\Model\Entity\Tenant}
     */
    private function createUserAndTenant(): array
    {
        $suffix = bin2hex(random_bytes(6));
        $roles = TableRegistry::getTableLocator()->get('Roles');
        $profiles = TableRegistry::getTableLocator()->get('Profiles');
        $users = TableRegistry::getTableLocator()->get('Users');
        $tenants = TableRegistry::getTableLocator()->get('Tenants');

        $role = $roles->newEntity([
            'name' => 'tenant_context_controller_' . $suffix,
            'is_super_admin' => false,
        ]);
        $roles->saveOrFail($role);

        $profile = $profiles->newEntity(['name' => 'tenant_context_controller_' . $suffix]);
        $profiles->saveOrFail($profile);

        $user = $users->newEntity([
            'username' => 'tenant_context_controller_' . $suffix,
            'password' => 'Password123',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
            'active' => true,
        ]);
        $users->saveOrFail($user);

        $tenant = $tenants->newEntity([
            'name' => 'Tenant ' . $suffix,
            'status' => 'active',
        ]);
        $tenants->saveOrFail($tenant);

        return [$user, $tenant];
    }
}
