<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\TenantMembershipsTable;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query\SelectQuery;

final class TenantContextResolver
{
    /** Receives the membership table used to validate each context request. */
    public function __construct(
        private readonly TenantMembershipsTable $memberships,
    ) {
    }

    /** Resolves an active tenant context for an authenticated user or denies access. */
    public function resolve(int $userId, int $tenantId): TenantContext
    {
        if ($userId < 1 || $tenantId < 1) {
            throw new ForbiddenException('Tenant context is not available.');
        }

        $now = FrozenTime::now();
        $membership = $this->memberships->find()
            ->where([
                'TenantMemberships.user_id' => $userId,
                'TenantMemberships.tenant_id' => $tenantId,
                'TenantMemberships.status' => 'active',
                'OR' => [
                    ['TenantMemberships.starts_at IS' => null],
                    ['TenantMemberships.starts_at <=' => $now],
                ],
                'AND' => [
                    [
                        'OR' => [
                            ['TenantMemberships.ends_at IS' => null],
                            ['TenantMemberships.ends_at >' => $now],
                        ],
                    ],
                ],
            ])
            ->innerJoinWith('Tenants', function (SelectQuery $query): SelectQuery {
                return $query->where(['Tenants.status' => 'active']);
            })
            ->innerJoinWith('Users', function (SelectQuery $query): SelectQuery {
                return $query->where(['Users.active' => true]);
            })
            ->first();

        if ($membership === null) {
            throw new ForbiddenException('Tenant context is not available.');
        }

        return new TenantContext($userId, $tenantId);
    }
}
