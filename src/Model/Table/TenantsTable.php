<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class TenantsTable extends Table
{
    /** Configures tenant associations and persistence. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('tenants');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');
        $this->hasMany('TenantMemberships', ['foreignKey' => 'tenant_id']);
    }

    /** Validates tenant names and lifecycle states. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 180)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->inList('status', ['active', 'suspended']);

        return $validator;
    }
}
