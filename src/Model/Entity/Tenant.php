<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Tenant extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'status' => true,
    ];
}
