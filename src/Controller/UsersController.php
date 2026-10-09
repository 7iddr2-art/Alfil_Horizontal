<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\TenantContextSession;
use App\Service\TenantContextResolver;
use Cake\Http\Response;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['login']);
    }

    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();

        if ($this->request->is('post')) {
            if ($result->isValid()) {
                $this->tenantContextSession()->clear($this->request);
                $this->recordAccessLogin();

                return $this->Authentication->redirectAfterLogin('/');
            }

            $this->Flash->error(__('Usuario o contraseña incorrectos.'));
        }

        $this->set('authenticationResult', $result);

        return null;
    }

    public function logout(): Response
    {
        $this->recordAccessLogout();
        $this->tenantContextSession()->clear($this->request);
        $this->Authentication->logout();

        return $this->redirect('/users/login');
    }

    protected function recordAccessLogin(): void
    {
        $identity = $this->Authentication->getIdentity();
        if ($identity === null) {
            return;
        }

        $userId = $identity->getIdentifier();
        if ($userId === null) {
            return;
        }

        $this->fetchTable('AccessAudits')->recordLogin((int)$userId, [
            'user_name' => $identity->get('username') ?? $identity->get('user_name') ?? null,
            'session_id' => session_id(),
            'ip_address' => $this->request->clientIp() ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $this->request->getHeaderLine('User-Agent') ?: ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'login_method' => 'form',
        ]);
    }

    /** Creates the helper that stores and clears the authenticated user's tenant selection. */
    private function tenantContextSession(): TenantContextSession
    {
        $resolver = new TenantContextResolver($this->fetchTable('TenantMemberships'));

        return new TenantContextSession($resolver);
    }

    protected function recordAccessLogout(): void
    {
        $identity = $this->Authentication->getIdentity();
        if ($identity === null) {
            return;
        }

        $userId = $identity->getIdentifier();
        if ($userId === null) {
            return;
        }

        $this->fetchTable('AccessAudits')->recordLogout((int)$userId, [
            'user_name' => $identity->get('username') ?? $identity->get('user_name') ?? null,
            'session_id' => session_id(),
            'ip_address' => $this->request->clientIp() ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $this->request->getHeaderLine('User-Agent') ?: ($_SERVER['HTTP_USER_AGENT'] ?? null),
            'login_method' => 'form',
        ]);
    }

    public function index(): ?Response
    {
        $this->set('users', $this->Users->find()->contain(['Roles', 'Profiles'])->all());

        return null;
    }

    public function add(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Usuario registrado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar el usuario.'));
        }
        $this->set(compact('user'));
        $this->set('roles', $this->fetchTable('Roles')->find('list')->all());
        $this->set('profiles', $this->fetchTable('Profiles')->find('list')->all());

        return null;
    }

    public function edit(int $id): ?Response
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $user = $this->Users->patchEntity($user, $data, ['associated' => []]);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Usuario actualizado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar el usuario.'));
        }
        $this->set(compact('user'));
        $this->set('roles', $this->fetchTable('Roles')->find('list')->all());
        $this->set('profiles', $this->fetchTable('Profiles')->find('list')->all());

        return null;
    }

    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('Usuario eliminado correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar el usuario.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
