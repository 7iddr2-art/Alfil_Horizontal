<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PersonalAdministracionesTable extends Table
{
    /** Configures personnel associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('personal_administraciones');
        $this->setPrimaryKey('id');
        $this->setDisplayField('full_name');
        $this->belongsTo('PropiedadesHorizontales', [
            'foreignKey' => 'propiedad_horizontal_id',
            'joinType' => 'INNER',
        ]);
    }

    /** Defines validation rules for personnel records. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('propiedad_horizontal_id')->notEmptyString('propiedad_horizontal_id')
            ->notEmptyString('document_type')->maxLength('document_type', 20)
            ->notEmptyString('document_number')->maxLength('document_number', 30)
            ->notEmptyString('full_name')->maxLength('full_name', 180)
            ->notEmptyString('position')->maxLength('position', 100)
            ->date('start_date', ['ymd'])->notEmptyDate('start_date')
            ->date('end_date', ['ymd'])->allowEmptyDate('end_date')
            ->email('email', false, 'Ingrese un correo electrónico válido.')
            ->allowEmptyString('email');

        return $validator;
    }
}
