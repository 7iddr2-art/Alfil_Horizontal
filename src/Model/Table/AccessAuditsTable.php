<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\AccessAudit;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query\DeleteQuery;
use Cake\ORM\Table;
use DateTimeImmutable;
use InvalidArgumentException;

class AccessAuditsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('access_audits');
        $this->setPrimaryKey('id');
        $this->belongsTo('Users');
    }

    public function recordLogin(int $userId, array $data = []): AccessAudit
    {
        $now = new FrozenTime();
        $loginAt = $now->format('Y-m-d H:i:s');

        $record = $this->newEntity([
            'user_id' => $userId,
            'username' => $data['user_name'] ?? null,
            'session_id' => $data['session_id'] ?? session_id(),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'login_at' => $loginAt,
            'logout_at' => null,
            'status' => 'connected',
            'duration_seconds' => null,
            'login_method' => $data['login_method'] ?? 'form',
            'last_activity_at' => $loginAt,
            'created' => $loginAt,
            'modified' => $loginAt,
        ]);

        $saved = $this->saveOrFail($record);

        return $saved;
    }

    public function recordLogout(int $userId, array $data = []): AccessAudit
    {
        $now = new FrozenTime();
        $logoutAt = $now->format('Y-m-d H:i:s');

        $conditions = [
            'user_id' => $userId,
            'status' => 'connected',
            'logout_at IS' => null,
        ];
        if (!empty($data['session_id'])) {
            $conditions['session_id'] = $data['session_id'];
        }

        $activeAudit = $this->find()
            ->where($conditions)
            ->orderByDesc('login_at')
            ->first();

        if (!$activeAudit) {
            $activeAudit = $this->newEntity([
                'user_id' => $userId,
                'username' => $data['user_name'] ?? null,
                'session_id' => $data['session_id'] ?? session_id(),
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'login_at' => $logoutAt,
                'status' => 'disconnected',
                'login_method' => $data['login_method'] ?? 'form',
                'last_activity_at' => $logoutAt,
                'created' => $logoutAt,
                'modified' => $logoutAt,
            ]);
        }

        $activeAudit->set('logout_at', $logoutAt);
        $activeAudit->set('status', 'disconnected');
        $activeAudit->set('session_id', $data['session_id'] ?? $activeAudit->session_id ?? session_id());
        $activeAudit->set('ip_address', $data['ip_address'] ?? $activeAudit->ip_address ?? null);
        $activeAudit->set('user_agent', $data['user_agent'] ?? $activeAudit->user_agent ?? null);
        $activeAudit->set('last_activity_at', $logoutAt);
        $activeAudit->set('modified', $logoutAt);

        $loginAt = $activeAudit->login_at !== null && $activeAudit->login_at !== ''
            ? new FrozenTime($activeAudit->login_at)
            : $now;
        $activeAudit->set('duration_seconds', max(0, $loginAt->diffInSeconds($now)));

        $saved = $this->saveOrFail($activeAudit);

        return $saved;
    }

    public function markExpiredConnections(int $timeoutMinutes): int
    {
        if ($timeoutMinutes <= 0) {
            return 0;
        }

        $now = new FrozenTime();
        $cutoff = $now->subMinutes($timeoutMinutes);

        return $this->updateQuery()
            ->set([
                'status' => 'missing_logout',
                'modified' => $now,
            ])
            ->where([
                'status' => 'connected',
                'logout_at IS' => null,
                'OR' => [
                    ['last_activity_at <' => $cutoff],
                    ['last_activity_at IS' => null, 'login_at <' => $cutoff],
                ],
            ])
            ->execute()
            ->rowCount();
    }

    public function refreshSessionActivity(int $userId, string $sessionId): void
    {
        $now = new FrozenTime();

        $this->updateQuery()
            ->set([
                'last_activity_at' => $now,
                'modified' => $now,
            ])
            ->where([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'status' => 'connected',
                'logout_at IS' => null,
            ])
            ->execute();
    }

    public function deleteInactiveByDateRange(string $startDate, string $endDate): int
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);

        if (
            $start === false
            || $end === false
            || $start->format('Y-m-d') !== $startDate
            || $end->format('Y-m-d') !== $endDate
            || $start > $end
        ) {
            throw new InvalidArgumentException('Invalid audit cleanup date range.');
        }

        return $this->inactiveDeleteQuery()
            ->where([
                'login_at >=' => $start->format('Y-m-d 00:00:00'),
                'login_at <' => $end->modify('+1 day')->format('Y-m-d 00:00:00'),
            ])
            ->execute()
            ->rowCount();
    }

    public function deleteAllInactive(): int
    {
        return $this->inactiveDeleteQuery()
            ->execute()
            ->rowCount();
    }

    private function inactiveDeleteQuery(): DeleteQuery
    {
        return $this->deleteQuery()
            ->where([
                'OR' => [
                    ['status !=' => 'connected'],
                    ['status IS' => null],
                    ['logout_at IS NOT' => null],
                ],
            ]);
    }
}
