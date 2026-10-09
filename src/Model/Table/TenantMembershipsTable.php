<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class TenantMembershipsTable extends Table
{
    /** Configures tenant and identity associations. */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('tenant_memberships');
        $this->setPrimaryKey('id');
        $this->belongsTo('Tenants', ['foreignKey' => 'tenant_id', 'joinType' => 'INNER']);
        $this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
    }

    /** Validates membership status and effective dates. */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('tenant_id')
            ->requirePresence('tenant_id', 'create')
            ->notEmptyString('tenant_id')
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id')
            ->inList('status', ['invited', 'active', 'suspended', 'revoked'])
            ->dateTime('starts_at')
            ->allowEmptyDateTime('starts_at')
            ->dateTime('ends_at')
            ->allowEmptyDateTime('ends_at');

        return $validator;
    }

    /** Enforces one membership per user and tenant and validates both references. */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['tenant_id', 'user_id']));
        $rules->add($rules->existsIn(['tenant_id'], 'Tenants'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
