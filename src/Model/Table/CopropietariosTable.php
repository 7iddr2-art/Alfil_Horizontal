<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CopropietariosTable extends Table
{
    /** Configures table associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('copropietarios');
        $this->setPrimaryKey('id');
        $this->setDisplayField('document_number');
        $this->hasMany('ViviendaCopropietarios', [
            'foreignKey' => 'copropietario_id',
            'dependent' => false,
        ]);
        $this->belongsToMany('Viviendas', [
            'through' => 'ViviendaCopropietarios',
            'foreignKey' => 'copropietario_id',
            'targetForeignKey' => 'vivienda_id',
        ]);
    }

    /** Defines validation rules for co-owner records. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('document_type', 'Seleccione el tipo de documento.')
            ->maxLength('document_type', 20)
            ->notEmptyString('document_number', 'Ingrese el número de documento.')
            ->maxLength('document_number', 30)
            ->notEmptyString('first_name', 'Ingrese al menos un nombre.')
            ->maxLength('first_name', 80)
            ->notEmptyString('last_name', 'Ingrese al menos un apellido.')
            ->maxLength('last_name', 80)
            ->email('email', false, 'Ingrese un correo electrónico válido.')
            ->allowEmptyString('email')
            ->date('birth_date', ['ymd'])
            ->allowEmptyDate('birth_date');

        return $validator;
    }
}
