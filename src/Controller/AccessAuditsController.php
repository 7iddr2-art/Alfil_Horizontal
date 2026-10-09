<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\ORM\Query;
use InvalidArgumentException;

class AccessAuditsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication', [
            'requireIdentity' => true,
        ]);
    }

    public function index(): ?Response
    {
        $filters = $this->getFiltersFromRequest();
        $query = $this->buildAuditQuery($filters);

        $this->set(compact('filters'));
        $this->set('accessAudits', $this->paginate($query, ['limit' => 25, 'maxLimit' => 100]));

        return null;
    }

    public function cleanup(): Response
    {
        $this->request->allowMethod(['post']);

        $accessAudits = $this->fetchTable('AccessAudits');
        $mode = (string)$this->request->getData('cleanup_mode', '');

        if ($mode === 'range') {
            try {
                $deletedCount = $accessAudits->deleteInactiveByDateRange(
                    (string)$this->request->getData('cleanup_start_date', ''),
                    (string)$this->request->getData('cleanup_end_date', ''),
                );
            } catch (InvalidArgumentException) {
                $this->Flash->error(__('El rango de fechas no es válido.'));

                return $this->redirect(['action' => 'index']);
            }
        } elseif ($mode === 'all' && (string)$this->request->getData('confirm_all', '') === '1') {
            $deletedCount = $accessAudits->deleteAllInactive();
        } else {
            $this->Flash->error(__('Confirma la operación de limpieza solicitada.'));

            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->success(__('Se eliminaron {0} registros de auditoría.', $deletedCount));

        return $this->redirect(['action' => 'index']);
    }

    public function exportCsv(): Response
    {
        $filters = $this->getFiltersFromRequest();
        $query = $this->buildAuditQuery($filters)
            ->limit(5000);

        $rows = $query->all();
        $handle = fopen('php://temp', 'wb+');
        fputcsv($handle, ['Usuario', 'Sesión', 'Estado', 'IP', 'Conexión', 'Desconexión', 'Duración (s)', 'Última actividad', 'Método']);

        foreach ($rows as $audit) {
            fputcsv($handle, [
                $audit->username ?? $audit->user->username ?? 'N/A',
                $audit->session_id ?? 'N/A',
                match ($audit->status) {
                    'connected' => 'Conectado',
                    'disconnected' => 'Desconectado',
                    'missing_logout' => 'Cierre no registrado',
                    default => 'Desconocido',
                },
                $audit->ip_address ?? 'N/A',
                $audit->login_at ? $audit->login_at->format('Y-m-d H:i:s') : 'N/A',
                $audit->logout_at ? $audit->logout_at->format('Y-m-d H:i:s') : 'N/A',
                $audit->duration_seconds ?? 'N/A',
                $audit->last_activity_at ? $audit->last_activity_at->format('Y-m-d H:i:s') : 'N/A',
                $audit->login_method ?? 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->withType('csv')
            ->withHeader('Content-Disposition', 'attachment; filename="auditoria_accesos.csv"')
            ->withStringBody((string)$csv);
    }

    /**
     * @param array<string, string> $filters
     * @return \Cake\ORM\Query
     */
    protected function buildAuditQuery(array $filters): Query
    {
        $query = $this->fetchTable('AccessAudits')->find()
            ->contain(['Users'])
            ->orderByDesc('login_at');

        if ($filters['user'] !== '') {
            $query->where(['OR' => [
                'AccessAudits.username LIKE' => '%' . $filters['user'] . '%',
                'Users.username LIKE' => '%' . $filters['user'] . '%',
            ]]);
        }

        if ($filters['session'] !== '') {
            $query->where(['AccessAudits.session_id LIKE' => '%' . $filters['session'] . '%']);
        }

        if ($filters['status'] !== '') {
            $query->where(['AccessAudits.status' => $filters['status']]);
        }

        if ($filters['start_date'] !== '') {
            $query->where(['AccessAudits.login_at >=' => $filters['start_date'] . ' 00:00:00']);
        }

        if ($filters['end_date'] !== '') {
            $query->where(['AccessAudits.login_at <=' => $filters['end_date'] . ' 23:59:59']);
        }

        return $query;
    }

    /**
     * @return array<string, string>
     */
    protected function getFiltersFromRequest(): array
    {
        return [
            'user' => (string)($this->request->getQuery('user') ?? ''),
            'session' => (string)($this->request->getQuery('session') ?? ''),
            'status' => (string)($this->request->getQuery('status') ?? ''),
            'start_date' => (string)($this->request->getQuery('start_date') ?? ''),
            'end_date' => (string)($this->request->getQuery('end_date') ?? ''),
        ];
    }
}
