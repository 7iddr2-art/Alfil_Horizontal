<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateTenantMembershipTables extends BaseMigration
{
    /** Creates empty tenant and user-membership tables without seeding records. */
    public function change(): void
    {
        $this->table('tenants')
            ->addColumn('name', 'string', ['limit' => 180])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addTimestamps()
            ->addIndex(['status'])
            ->create();

        $this->table('tenant_memberships')
            ->addColumn('tenant_id', 'integer')
            ->addColumn('user_id', 'integer')
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'invited'])
            ->addColumn('starts_at', 'datetime', ['null' => true])
            ->addColumn('ends_at', 'datetime', ['null' => true])
            ->addTimestamps()
            ->addIndex(['tenant_id', 'user_id'], ['unique' => true])
            ->addIndex(['tenant_id', 'status'])
            ->addIndex(['user_id', 'status'])
            ->addForeignKey('tenant_id', 'tenants', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
