<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PropiedadesHorizontalesTable extends Table
{
    /** Configures property associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('propiedades_horizontales');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');
        $this->hasMany('PersonalAdministraciones', ['foreignKey' => 'propiedad_horizontal_id']);
        $this->hasMany('PolizasAseguramiento', ['foreignKey' => 'propiedad_horizontal_id']);
    }

    /** Defines validation rules for horizontal-property records. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', 'Ingrese el nombre de la propiedad horizontal.')
            ->maxLength('name', 180)
            ->notEmptyString('nit', 'Ingrese el NIT.')
            ->maxLength('nit', 20)
            ->email('email', false, 'Ingrese un correo electrónico válido.')
            ->allowEmptyString('email')
            ->email('administrator_email', false, 'Ingrese un correo electrónico válido.')
            ->allowEmptyString('administrator_email');

        return $validator;
    }
}
