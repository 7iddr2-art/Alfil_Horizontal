<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('users');
        $this->setPrimaryKey('id');
        $this->belongsTo('Roles');
        $this->belongsTo('Profiles');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->scalar('username')->minLength('username', 3)->maxLength('username', 100)->requirePresence('username', 'create')->notEmptyString('username');
        $validator->scalar('password')->minLength('password', 8)->maxLength('password', 255)->requirePresence('password', 'create')->notEmptyString('password')->allowEmptyString('password', 'update');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->existsIn(['role_id'], 'Roles'));
        $rules->add($rules->existsIn(['profile_id'], 'Profiles'));

        return $rules;
    }
}