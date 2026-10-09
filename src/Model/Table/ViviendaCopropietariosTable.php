<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ViviendaCopropietariosTable extends Table
{
    /** Configures table associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('vivienda_copropietarios');
        $this->setPrimaryKey('id');
        $this->belongsTo('Viviendas', ['foreignKey' => 'vivienda_id', 'joinType' => 'INNER']);
        $this->belongsTo('Copropietarios', ['foreignKey' => 'copropietario_id', 'joinType' => 'INNER']);
    }

    /** Defines validation rules for home and co-owner associations. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('vivienda_id')->notEmptyString('vivienda_id', 'Seleccione una vivienda.')
            ->integer('copropietario_id')->notEmptyString('copropietario_id', 'Seleccione un copropietario.')
            ->notEmptyString('relationship_type', 'Seleccione la calidad de la relación.')
            ->inList('relationship_type', ['owner', 'usufructuary', 'legal_representative'])
            ->decimal('ownership_percentage')
            ->range('ownership_percentage', [0, 100], 'El porcentaje debe estar entre 0 y 100.')
            ->allowEmptyString('ownership_percentage')
            ->date('start_date', ['ymd'])->allowEmptyDate('start_date')
            ->date('end_date', ['ymd'])->allowEmptyDate('end_date');

        return $validator;
    }
}
