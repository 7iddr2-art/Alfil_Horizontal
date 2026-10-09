<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ViviendasTable extends Table
{
    /** Configures table associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('viviendas');
        $this->setPrimaryKey('id');
        $this->setDisplayField('code');
        $this->hasMany('ViviendaCopropietarios', [
            'foreignKey' => 'vivienda_id',
            'dependent' => false,
        ]);
        $this->belongsToMany('Copropietarios', [
            'through' => 'ViviendaCopropietarios',
            'foreignKey' => 'vivienda_id',
            'targetForeignKey' => 'copropietario_id',
        ]);
    }

    /** Defines validation rules for home records. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('code', 'Ingrese el código de la vivienda.')
            ->maxLength('code', 30)
            ->notEmptyString('type', 'Seleccione el tipo de vivienda.')
            ->inList('type', ['apartment', 'house'], 'Seleccione un tipo válido.')
            ->notEmptyString('number', 'Ingrese el número de la vivienda.')
            ->decimal('area_m2')
            ->greaterThanOrEqual('area_m2', 0)
            ->allowEmptyString('area_m2')
            ->decimal('coownership_percentage')
            ->range('coownership_percentage', [0, 100], 'El porcentaje debe estar entre 0 y 100.')
            ->allowEmptyString('coownership_percentage');

        return $validator;
    }
}
