<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;

class AccessAuditsTableTest extends TestCase
{
    public function testRecordLoginAndLogout(): void
    {
        $roles = TableRegistry::getTableLocator()->get('Roles');
        $profiles = TableRegistry::getTableLocator()->get('Profiles');
        $users = TableRegistry::getTableLocator()->get('Users');

        $role = $roles->find()->first();
        if ($role === null) {
            $role = $roles->newEntity([
                'name' => 'auditor_role',
                'description' => 'Role for auditor tests',
                'is_super_admin' => false,
            ]);
            $roles->saveOrFail($role);
        }

        $profile = $profiles->find()->first();
        if ($profile === null) {
            $profile = $profiles->newEntity([
                'name' => 'auditor_profile',
                'description' => 'Profile for auditor tests',
            ]);
            $profiles->saveOrFail($profile);
        }

        $user = $users->newEntity([
            'username' => 'auditor_test_user',
            'password' => 'Password123',
            'role_id' => (int)$role->id,
            'profile_id' => (int)$profile->id,
            'active' => true,
        ]);
        $users->saveOrFail($user);

        $accessAudits = TableRegistry::getTableLocator()->get('AccessAudits');

        $loginAudit = $accessAudits->recordLogin((int)$user->id, [
            'user_name' => $user->username,
            'session_id' => 'session-123',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertNotEmpty($loginAudit->id);
        $this->assertNotNull($loginAudit->login_at);
        $this->assertNull($loginAudit->logout_at);
        $this->assertSame('connected', $loginAudit->status);

        $logoutAudit = $accessAudits->recordLogout((int)$user->id, [
            'session_id' => 'session-123',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertNotNull($logoutAudit->logout_at);
        $this->assertSame('disconnected', $logoutAudit->status);
        $this->assertNotNull($logoutAudit->duration_seconds);

        $orphanedAudit = $accessAudits->recordLogin((int)$user->id, [
            'user_name' => $user->username,
            'session_id' => 'session-456',
        ]);
        $orphanedAudit->set('last_activity_at', (new FrozenTime())->subMinutes(31));
        $accessAudits->saveOrFail($orphanedAudit);

        $activeAudit = $accessAudits->recordLogin((int)$user->id, [
            'user_name' => $user->username,
            'session_id' => 'session-789',
        ]);
        $activeAudit->set('last_activity_at', (new FrozenTime())->subMinutes(31));
        $accessAudits->saveOrFail($activeAudit);
        $accessAudits->refreshSessionActivity((int)$user->id, 'session-789');

        $this->assertSame(1, $accessAudits->markExpiredConnections(30));

        $orphanedAudit = $accessAudits->get($orphanedAudit->id);
        $this->assertSame('missing_logout', $orphanedAudit->status);
        $this->assertNull($orphanedAudit->logout_at);

        $activeAudit = $accessAudits->get($activeAudit->id);
        $this->assertSame('connected', $activeAudit->status);
        $this->assertNull($activeAudit->logout_at);

        $historicalLoginAt = (new FrozenTime())->subDays(2);
        $cleanupDate = $historicalLoginAt->format('Y-m-d');
        $orphanedAudit->set('login_at', $historicalLoginAt);
        $activeAudit->set('login_at', $historicalLoginAt);
        $accessAudits->saveOrFail($orphanedAudit);
        $accessAudits->saveOrFail($activeAudit);

        $this->assertSame(1, $accessAudits->deleteInactiveByDateRange($cleanupDate, $cleanupDate));
        $this->assertSame(0, $accessAudits->find()->where(['id' => $orphanedAudit->id])->count());
        $this->assertSame('connected', $accessAudits->get($activeAudit->id)->status);

        $this->assertGreaterThanOrEqual(1, $accessAudits->deleteAllInactive());
        $this->assertSame('connected', $accessAudits->get($activeAudit->id)->status);
    }
}
