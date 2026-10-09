<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAccessAuditTable extends BaseMigration
{
    public function change(): void
    {
        $this->table('access_audits')
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('username', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('session_id', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('user_agent', 'text', ['null' => true])
            ->addColumn('login_at', 'datetime', ['null' => true])
            ->addColumn('logout_at', 'datetime', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 30, 'default' => 'connected'])
            ->addColumn('duration_seconds', 'integer', ['null' => true])
            ->addColumn('login_method', 'string', ['limit' => 50, 'default' => 'form'])
            ->addColumn('last_activity_at', 'datetime', ['null' => true])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'])
            ->addIndex(['status'])
            ->addIndex(['login_at'])
            ->addIndex(['logout_at'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
