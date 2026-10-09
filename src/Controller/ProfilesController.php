<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class ProfilesController extends AppController
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
        $this->set('profiles', $this->Profiles->find()->contain(['Users'])->all());

        return null;
    }

    public function add(): ?Response
    {
        $profile = $this->Profiles->newEmptyEntity();
        if ($this->request->is('post')) {
            $profile = $this->Profiles->patchEntity($profile, $this->request->getData());
            if ($this->Profiles->save($profile)) {
                $this->Flash->success(__('Perfil creado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo crear el perfil.'));
        }
        $this->set(compact('profile'));

        return null;
    }

    public function edit(int $id): ?Response
    {
        $profile = $this->Profiles->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $profile = $this->Profiles->patchEntity($profile, $this->request->getData());
            if ($this->Profiles->save($profile)) {
                $this->Flash->success(__('Perfil actualizado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar el perfil.'));
        }
        $this->set(compact('profile'));

        return null;
    }

    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $profile = $this->Profiles->get($id);
        if ($this->Profiles->delete($profile)) {
            $this->Flash->success(__('Perfil eliminado correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar el perfil.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}