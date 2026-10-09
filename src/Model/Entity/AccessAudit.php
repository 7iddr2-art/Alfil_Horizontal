<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class AccessAudit extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'username' => true,
        'session_id' => true,
        'ip_address' => true,
        'user_agent' => true,
        'login_at' => true,
        'logout_at' => true,
        'status' => true,
        'duration_seconds' => true,
        'login_method' => true,
        'last_activity_at' => true,
        'created' => true,
        'modified' => true,
    ];
}
