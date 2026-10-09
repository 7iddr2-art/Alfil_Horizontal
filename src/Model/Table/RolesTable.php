<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class RolesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('roles');
        $this->setPrimaryKey('id');
        $this->hasMany('Users');
    }
}