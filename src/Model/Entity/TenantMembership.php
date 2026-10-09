<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class TenantMembership extends Entity
{
    protected array $_accessible = [
        'tenant_id' => true,
        'user_id' => true,
        'status' => true,
        'starts_at' => true,
        'ends_at' => true,
    ];
}
