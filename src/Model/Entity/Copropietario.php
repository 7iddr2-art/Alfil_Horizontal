<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Copropietario extends Entity
{
    protected array $_accessible = [
        'document_type' => true, 'document_number' => true, 'first_name' => true,
        'middle_name' => true, 'last_name' => true, 'second_last_name' => true,
        'birth_date' => true, 'gender' => true, 'nationality' => true,
        'marital_status' => true, 'email' => true, 'mobile_phone' => true,
        'landline_phone' => true, 'correspondence_address' => true,
        'emergency_contact_name' => true, 'emergency_contact_phone' => true,
        'active' => true,
    ];

    /** Returns the co-owner's complete name. */
    protected function _getFullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name, $this->middle_name, $this->last_name, $this->second_last_name,
        ])));
    }
}
