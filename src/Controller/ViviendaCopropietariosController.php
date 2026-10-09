<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class ViviendaCopropietariosController extends AppController
{
    /** Lists home and co-owner associations. */
    public function index(): ?Response
    {
        $this->set('asociaciones', $this->ViviendaCopropietarios->find()
            ->contain(['Viviendas', 'Copropietarios'])
            ->orderBy(['Viviendas.code' => 'ASC', 'Copropietarios.last_name' => 'ASC'])
            ->all());

        return null;
    }

    /** Creates a home and co-owner association. */
    public function add(): ?Response
    {
        $asociacion = $this->ViviendaCopropietarios->newEmptyEntity();
        if ($this->request->is('post')) {
            $asociacion = $this->ViviendaCopropietarios->patchEntity($asociacion, $this->request->getData());
            if ($this->ViviendaCopropietarios->save($asociacion)) {
                $this->Flash->success(__('Asociación creada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo crear la asociación. Verifique que no exista previamente.'));
        }
        $this->set(compact('asociacion'));
        $this->setFormLists();

        return null;
    }

    /** Updates a home and co-owner association. */
    public function edit(int $id): ?Response
    {
        $asociacion = $this->ViviendaCopropietarios->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $asociacion = $this->ViviendaCopropietarios->patchEntity($asociacion, $this->request->getData());
            if ($this->ViviendaCopropietarios->save($asociacion)) {
                $this->Flash->success(__('Asociación actualizada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar la asociación.'));
        }
        $this->set(compact('asociacion'));
        $this->setFormLists();

        return null;
    }

    /** Deletes a home and co-owner association. */
    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->ViviendaCopropietarios->delete($this->ViviendaCopropietarios->get($id))) {
            $this->Flash->success(__('Asociación eliminada correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar la asociación.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /** Provides the options needed by association forms. */
    private function setFormLists(): void
    {
        $viviendas = $this->fetchTable('Viviendas')
            ->find('list')
            ->orderBy(['code' => 'ASC'])
            ->all();
        $owners = $this->fetchTable('Copropietarios')
            ->find()
            ->orderBy(['last_name' => 'ASC', 'first_name' => 'ASC'])
            ->all();

        $this->set('viviendas', $viviendas);
        $this->set(
            'copropietarios',
            $owners->combine('id', fn($owner) => $owner->full_name . ' — ' . $owner->document_number),
        );
    }
}
