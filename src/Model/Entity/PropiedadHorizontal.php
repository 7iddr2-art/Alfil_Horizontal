<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PropiedadHorizontal extends Entity
{
    protected array $_accessible = [
        'name' => true, 'nit' => true, 'verification_digit' => true, 'tax_regime' => true,
        'address' => true, 'city' => true, 'department' => true, 'phone' => true, 'email' => true,
        'administrator_name' => true, 'administrator_document' => true, 'administrator_phone' => true,
        'administrator_email' => true, 'legal_representative_name' => true,
        'legal_representative_document' => true, 'fiscal_reviewer_name' => true,
        'fiscal_reviewer_document' => true, 'fiscal_reviewer_card' => true, 'builder_name' => true,
        'builder_nit' => true, 'active' => true, 'notes' => true,
    ];
}
