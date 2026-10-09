<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\TenantContextResolver;
use App\Service\TenantContextSession;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query\SelectQuery;

class TenantContextsController extends AppController
{
    /** Lists tenants the authenticated user can currently select. */
    public function index(): ?Response
    {
        $identity = $this->Authentication->getIdentity();
        $userId = (int)$identity->getIdentifier();
        $now = FrozenTime::now();
        $memberships = $this->fetchTable('TenantMemberships');
        $availableTenants = $memberships->find()
            ->contain(['Tenants' => function (SelectQuery $query): SelectQuery {
                return $query->where(['Tenants.status' => 'active']);
            }])
            ->where([
                'TenantMemberships.user_id' => $userId,
                'TenantMemberships.status' => 'active',
                'OR' => [
                    ['TenantMemberships.starts_at IS' => null],
                    ['TenantMemberships.starts_at <=' => $now],
                ],
                'AND' => [[
                    'OR' => [
                        ['TenantMemberships.ends_at IS' => null],
                        ['TenantMemberships.ends_at >' => $now],
                    ],
                ]],
            ])
            ->innerJoinWith('Tenants', function (SelectQuery $query): SelectQuery {
                return $query->where(['Tenants.status' => 'active']);
            })
            ->all();

        $this->set('availableTenants', $availableTenants);
        $this->set('tenantContext', $this->request->getAttribute('tenantContext'));

        return null;
    }

    /** Validates a tenant selection and stores it in the authenticated session. */
    public function select(): Response
    {
        $this->request->allowMethod(['post']);
        $identity = $this->Authentication->getIdentity();
        $tenantId = filter_var($this->request->getData('tenant_id'), FILTER_VALIDATE_INT);
        if ($tenantId === false || $tenantId < 1) {
            $this->Flash->error(__('Seleccione un tenant válido.'));

            return $this->redirect(['action' => 'index']);
        }

        $resolver = new TenantContextResolver($this->fetchTable('TenantMemberships'));

        try {
            $context = $resolver->resolve((int)$identity->getIdentifier(), $tenantId);
        } catch (ForbiddenException) {
            $this->Flash->error(__('No tiene acceso a ese tenant.'));

            return $this->redirect(['action' => 'index']);
        }

        (new TenantContextSession($resolver))->select($this->request, $context);
        $this->Flash->success(__('Tenant activo actualizado.'));

        return $this->redirect(['action' => 'index']);
    }
}
