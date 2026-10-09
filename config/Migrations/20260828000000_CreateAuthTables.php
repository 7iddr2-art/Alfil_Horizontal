<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAuthTables extends BaseMigration
{
    public function change(): void
    {
        $this->table('roles')
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('is_super_admin', 'boolean', ['default' => false])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('profiles')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('users')
            ->addColumn('username', 'string', ['limit' => 100])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('role_id', 'integer')
            ->addColumn('profile_id', 'integer')
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['username'], ['unique' => true])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('profile_id', 'profiles', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $this->table('roles')->insert([
            'name' => 'super_admin',
            'description' => 'Acceso total al sistema',
            'is_super_admin' => true,
            'created' => $now,
            'modified' => $now,
        ])->saveData();

        $this->table('profiles')->insert([
            'name' => 'Administrador',
            'description' => 'Perfil principal de administración',
            'created' => $now,
            'modified' => $now,
        ])->saveData();

        $roleId = $this->fetchRow('SELECT id FROM roles WHERE name = \'super_admin\'')['id'];
        $profileId = $this->fetchRow('SELECT id FROM profiles WHERE name = \'Administrador\'')['id'];

        $this->table('users')->insert([
            'username' => 'Admin',
            'password' => '$2y$10$ipbVbIaYPNOiT3Ss9TFWfOE4CUGN9V54XUYSd4q1gZPcJjmezNxdq',
            'role_id' => $roleId,
            'profile_id' => $profileId,
            'active' => true,
            'created' => $now,
            'modified' => $now,
        ])->saveData();
    }
}
