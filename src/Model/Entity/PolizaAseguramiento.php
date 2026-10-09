<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PolizaAseguramiento extends Entity
{
    protected array $_accessible = [
        'propiedad_horizontal_id' => true, 'insurer' => true, 'policy_number' => true,
        'coverage_type' => true, 'coverage_description' => true, 'insured_value' => true,
        'premium_value' => true, 'start_date' => true, 'end_date' => true, 'status' => true,
        'notes' => true,
    ];
}
