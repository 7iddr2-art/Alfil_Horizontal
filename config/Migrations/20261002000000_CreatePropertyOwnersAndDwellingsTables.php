<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePropertyOwnersAndDwellingsTables extends BaseMigration
{
    /** Creates the co-owner, home, and association tables. */
    public function change(): void
    {
        $this->table('copropietarios')
            ->addColumn('document_type', 'string', ['limit' => 20])
            ->addColumn('document_number', 'string', ['limit' => 30])
            ->addColumn('first_name', 'string', ['limit' => 80])
            ->addColumn('middle_name', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('last_name', 'string', ['limit' => 80])
            ->addColumn('second_last_name', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('birth_date', 'date', ['null' => true])
            ->addColumn('gender', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('nationality', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('marital_status', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('mobile_phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('landline_phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('correspondence_address', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('emergency_contact_name', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('emergency_contact_phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps()
            ->addIndex(['document_type', 'document_number'], ['unique' => true])
            ->addIndex(['last_name', 'first_name'])
            ->create();

        $this->table('viviendas')
            ->addColumn('code', 'string', ['limit' => 30])
            ->addColumn('type', 'string', ['limit' => 20])
            ->addColumn('tower_block', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('number', 'string', ['limit' => 30])
            ->addColumn('floor', 'integer', ['null' => true])
            ->addColumn('area_m2', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('coownership_percentage', 'decimal', ['precision' => 7, 'scale' => 4, 'null' => true])
            ->addColumn('bedrooms', 'integer', ['null' => true])
            ->addColumn('bathrooms', 'integer', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps()
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['type', 'tower_block', 'number'], ['unique' => true])
            ->create();

        $this->table('vivienda_copropietarios')
            ->addColumn('vivienda_id', 'integer')
            ->addColumn('copropietario_id', 'integer')
            ->addColumn('relationship_type', 'string', ['limit' => 30, 'default' => 'owner'])
            ->addColumn('ownership_percentage', 'decimal', ['precision' => 7, 'scale' => 4, 'null' => true])
            ->addColumn('start_date', 'date', ['null' => true])
            ->addColumn('end_date', 'date', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps()
            ->addIndex(['vivienda_id', 'copropietario_id'], ['unique' => true])
            ->addIndex(['copropietario_id'])
            ->addForeignKey('vivienda_id', 'viviendas', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('copropietario_id', 'copropietarios', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
