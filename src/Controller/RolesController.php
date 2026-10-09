<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class RolesController extends AppController
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
        $this->set('roles', $this->Roles->find()->contain(['Users'])->all());

        return null;
    }

    public function add(): ?Response
    {
        $role = $this->Roles->newEmptyEntity();
        if ($this->request->is('post')) {
            $role = $this->Roles->patchEntity($role, $this->request->getData());
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Rol creado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo crear el rol.'));
        }
        $this->set(compact('role'));

        return null;
    }

    public function edit(int $id): ?Response
    {
        $role = $this->Roles->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $role = $this->Roles->patchEntity($role, $this->request->getData());
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Rol actualizado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar el rol.'));
        }
        $this->set(compact('role'));

        return null;
    }

    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $role = $this->Roles->get($id);
        if ($this->Roles->delete($role)) {
            $this->Flash->success(__('Rol eliminado correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar el rol.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}