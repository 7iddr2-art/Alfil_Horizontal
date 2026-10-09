<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class ProfilesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('profiles');
        $this->setPrimaryKey('id');
        $this->hasMany('Users');
    }
}