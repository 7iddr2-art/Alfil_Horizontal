<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateHorizontalPropertiesAdministrationTables extends BaseMigration
{
    /** Creates horizontal-property, personnel-history, and policy tables. */
    public function change(): void
    {
        $this->table('propiedades_horizontales')
            ->addColumn('name', 'string', ['limit' => 180])
            ->addColumn('nit', 'string', ['limit' => 20])
            ->addColumn('verification_digit', 'string', ['limit' => 2, 'null' => true])
            ->addColumn('tax_regime', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('address', 'string', ['limit' => 255])
            ->addColumn('city', 'string', ['limit' => 100])
            ->addColumn('department', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('administrator_name', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('administrator_document', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('administrator_phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('administrator_email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('legal_representative_name', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('legal_representative_document', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('fiscal_reviewer_name', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('fiscal_reviewer_document', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('fiscal_reviewer_card', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('builder_name', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('builder_nit', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps()
            ->addIndex(['nit'], ['unique' => true])
            ->create();

        $this->table('personal_administraciones')
            ->addColumn('propiedad_horizontal_id', 'integer')
            ->addColumn('document_type', 'string', ['limit' => 20])
            ->addColumn('document_number', 'string', ['limit' => 30])
            ->addColumn('full_name', 'string', ['limit' => 180])
            ->addColumn('position', 'string', ['limit' => 100])
            ->addColumn('employer', 'string', ['limit' => 160, 'null' => true])
            ->addColumn('employment_type', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps()
            ->addIndex(['propiedad_horizontal_id', 'document_number'])
            ->addIndex(['active'])
            ->addForeignKey('propiedad_horizontal_id', 'propiedades_horizontales', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();

        $this->table('polizas_aseguramiento')
            ->addColumn('propiedad_horizontal_id', 'integer')
            ->addColumn('insurer', 'string', ['limit' => 160])
            ->addColumn('policy_number', 'string', ['limit' => 80])
            ->addColumn('coverage_type', 'string', ['limit' => 100])
            ->addColumn('coverage_description', 'text', ['null' => true])
            ->addColumn('insured_value', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true])
            ->addColumn('premium_value', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true])
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date')
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps()
            ->addIndex(['propiedad_horizontal_id', 'policy_number'], ['unique' => true])
            ->addIndex(['end_date'])
            ->addForeignKey('propiedad_horizontal_id', 'propiedades_horizontales', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
