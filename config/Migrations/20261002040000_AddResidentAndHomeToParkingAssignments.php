<?php
declare(strict_types=1);
use Migrations\BaseMigration;
class AddResidentAndHomeToParkingAssignments extends BaseMigration
{
    /** Adds resident and home references to parking assignments. */
    public function change(): void
    {
        $this->table('asignaciones_parqueaderos')
            ->changeColumn('copropietario_id', 'integer', ['null' => true])
            ->addColumn('residente_id', 'integer', ['null' => true, 'after' => 'copropietario_id'])
            ->addColumn('vivienda_id', 'integer', ['null' => true, 'after' => 'residente_id'])
            ->addIndex(['residente_id'])
            ->addIndex(['vivienda_id'])
            ->addForeignKey('residente_id', 'residentes', 'id', ['delete' => 'RESTRICT'])
            ->addForeignKey('vivienda_id', 'viviendas', 'id', ['delete' => 'RESTRICT'])
            ->update();
    }
}
