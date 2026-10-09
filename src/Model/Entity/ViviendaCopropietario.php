<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ViviendaCopropietario extends Entity
{
    protected array $_accessible = [
        'vivienda_id' => true, 'copropietario_id' => true, 'relationship_type' => true,
        'ownership_percentage' => true, 'start_date' => true, 'end_date' => true,
        'active' => true, 'notes' => true,
    ];
}
