<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class ViviendasController extends AppController
{
    /** Searches homes by code for assignment forms. */
    public function search(): Response
    {
        $term = trim((string)$this->request->getQuery('q', ''));
        if ($term === '') {
            return $this->response->withType('application/json')->withStringBody('[]');
        }

        $items = $this->Viviendas->find()
            ->select(['id', 'code'])
            ->where(['Viviendas.code LIKE' => '%' . $term . '%'])
            ->orderBy(['Viviendas.code' => 'ASC'])
            ->limit(10)
            ->all();

        $results = [];
        foreach ($items as $item) {
            $results[] = ['id' => $item->id, 'label' => $item->code];
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($results, JSON_THROW_ON_ERROR));
    }

    /** Lists registered homes. */
    public function index(): ?Response
    {
        $this->set('viviendas', $this->Viviendas->find()->orderBy(['Viviendas.code' => 'ASC'])->all());

        return null;
    }

    /** Creates a home record. */
    public function add(): ?Response
    {
        $vivienda = $this->Viviendas->newEmptyEntity();
        if ($this->request->is('post')) {
            $vivienda = $this->Viviendas->patchEntity($vivienda, $this->request->getData());
            if ($this->Viviendas->save($vivienda)) {
                $this->Flash->success(__('Vivienda registrada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar la vivienda. Revise los datos ingresados.'));
        }
        $this->set(compact('vivienda'));

        return null;
    }

    /** Updates a home record. */
    public function edit(int $id): ?Response
    {
        $vivienda = $this->Viviendas->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $vivienda = $this->Viviendas->patchEntity($vivienda, $this->request->getData());
            if ($this->Viviendas->save($vivienda)) {
                $this->Flash->success(__('Vivienda actualizada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar la vivienda. Revise los datos ingresados.'));
        }
        $this->set(compact('vivienda'));

        return null;
    }

    /** Deletes a home when it has no co-owner associations. */
    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->fetchTable('ViviendaCopropietarios')->exists(['vivienda_id' => $id])) {
            $this->Flash->error(__('No se puede eliminar la vivienda porque tiene copropietarios asociados.'));

            return $this->redirect(['action' => 'index']);
        }

        if ($this->Viviendas->delete($this->Viviendas->get($id))) {
            $this->Flash->success(__('Vivienda eliminada correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar la vivienda.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
