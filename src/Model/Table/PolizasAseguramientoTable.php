<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PolizasAseguramientoTable extends Table
{
    /** Configures insurance-policy associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('polizas_aseguramiento');
        $this->setPrimaryKey('id');
        $this->setDisplayField('policy_number');
        $this->belongsTo('PropiedadesHorizontales', [
            'foreignKey' => 'propiedad_horizontal_id',
            'joinType' => 'INNER',
        ]);
    }

    /** Defines validation rules for insurance policies. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('propiedad_horizontal_id')->notEmptyString('propiedad_horizontal_id')
            ->notEmptyString('insurer')->maxLength('insurer', 160)
            ->notEmptyString('policy_number')->maxLength('policy_number', 80)
            ->notEmptyString('coverage_type')->maxLength('coverage_type', 100)
            ->date('start_date', ['ymd'])->notEmptyDate('start_date')
            ->date('end_date', ['ymd'])->notEmptyDate('end_date')
            ->decimal('insured_value')->allowEmptyString('insured_value')
            ->decimal('premium_value')->allowEmptyString('premium_value');

        return $validator;
    }
}
