<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PersonalAdministracion extends Entity
{
    protected array $_accessible = [
        'propiedad_horizontal_id' => true, 'document_type' => true, 'document_number' => true,
        'full_name' => true, 'position' => true, 'employer' => true, 'employment_type' => true,
        'email' => true, 'phone' => true, 'start_date' => true, 'end_date' => true,
        'active' => true, 'notes' => true,
    ];
}
