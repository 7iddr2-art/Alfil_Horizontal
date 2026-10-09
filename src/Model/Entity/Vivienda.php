<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Vivienda extends Entity
{
    protected array $_accessible = [
        'code' => true, 'type' => true, 'tower_block' => true, 'number' => true,
        'floor' => true, 'area_m2' => true, 'coownership_percentage' => true,
        'bedrooms' => true, 'bathrooms' => true, 'status' => true, 'notes' => true,
    ];
}
