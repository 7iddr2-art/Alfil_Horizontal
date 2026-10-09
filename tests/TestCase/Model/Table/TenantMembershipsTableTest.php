<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TenantMembershipsTableTest extends TestCase
{
    public function testTenantAndMembershipSchemasAndAssociations(): void
    {
        $tenants = TableRegistry::getTableLocator()->get('Tenants');
        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');
        $users = TableRegistry::getTableLocator()->get('Users');

        $this->assertTrue($tenants->getSchema()->hasColumn('name'));
        $this->assertTrue($tenants->getSchema()->hasColumn('status'));
        $this->assertTrue($memberships->getSchema()->hasColumn('tenant_id'));
        $this->assertTrue($memberships->getSchema()->hasColumn('user_id'));
        $this->assertTrue($memberships->getSchema()->hasColumn('starts_at'));
        $this->assertTrue($memberships->getSchema()->hasColumn('ends_at'));
        $this->assertSame(
            ['tenant_id', 'user_id'],
            $memberships->getSchema()->getConstraint('tenant_id')['columns'],
        );
        $this->assertSame(
            ['tenants', 'id'],
            $memberships->getSchema()->getConstraint('tenant_memberships_tenant_id')['references'],
        );
        $this->assertSame(
            ['users', 'id'],
            $memberships->getSchema()->getConstraint('tenant_memberships_user_id')['references'],
        );

        $this->assertNotNull($tenants->getAssociation('TenantMemberships'));
        $this->assertNotNull($memberships->getAssociation('Tenants'));
        $this->assertNotNull($memberships->getAssociation('Users'));
        $this->assertNotNull($users->getAssociation('TenantMemberships'));
    }

    public function testTenantValidationRejectsMissingNameAndUnknownStatus(): void
    {
        $tenants = TableRegistry::getTableLocator()->get('Tenants');

        $missingName = $tenants->newEntity(['status' => 'active']);
        $invalidStatus = $tenants->newEntity(['name' => 'Test tenant', 'status' => 'unknown']);

        $this->assertArrayHasKey('name', $missingName->getErrors());
        $this->assertArrayHasKey('status', $invalidStatus->getErrors());
    }

    public function testMembershipValidationRejectsInvalidState(): void
    {
        $memberships = TableRegistry::getTableLocator()->get('TenantMemberships');

        $invalidMembership = $memberships->newEntity([
            'tenant_id' => 1,
            'user_id' => 1,
            'status' => 'owner',
        ]);

        $this->assertArrayHasKey('status', $invalidMembership->getErrors());
    }
}
