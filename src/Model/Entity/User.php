<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'username' => true,
        'password' => true,
        'role_id' => true,
        'profile_id' => true,
        'active' => true,
    ];

    protected array $_hidden = ['password'];

    protected function _setPassword(?string $password): ?string
    {
        if ($password === null || $password === '') {
            return $password;
        }

        return (new DefaultPasswordHasher())->hash($password);
    }
}