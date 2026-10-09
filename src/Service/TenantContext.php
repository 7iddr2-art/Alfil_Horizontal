<?php
declare(strict_types=1);

namespace App\Service;

final readonly class TenantContext
{
    /** Creates an immutable tenant context for one authenticated identity. */
    public function __construct(
        public int $userId,
        public int $tenantId,
    ) {
    }
}
