<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class CopropietariosController extends AppController
{
    /** Searches co-owners by document or name for relationship forms. */
    public function search(): Response
    {
        $term = trim((string)$this->request->getQuery('q', ''));
        if (mb_strlen($term) < 2) {
            return $this->response->withType('application/json')->withStringBody('[]');
        }
        $like = '%' . $term . '%';
        $owners = $this->Copropietarios->find()->distinct(['Copropietarios.id'])->where(['OR' => [
            'document_number LIKE' => $like, 'first_name LIKE' => $like,
            'middle_name LIKE' => $like, 'last_name LIKE' => $like, 'second_last_name LIKE' => $like,
        ]])->limit(10)->all();
        $results = [];
        foreach ($owners as $owner) {
            $results[] = ['id' => $owner->id, 'label' => $owner->document_number . ' — ' . $owner->full_name];
        }

        return $this->response->withType('application/json')->withStringBody(json_encode($results));
    }

    /** Lists registered co-owners. */
    public function index(): ?Response
    {
        $this->set('copropietarios', $this->Copropietarios->find()->orderBy([
            'Copropietarios.last_name' => 'ASC', 'Copropietarios.first_name' => 'ASC',
        ])->all());

        return null;
    }

    /** Creates a co-owner record. */
    public function add(): ?Response
    {
        $copropietario = $this->Copropietarios->newEmptyEntity();
        if ($this->request->is('post')) {
            $copropietario = $this->Copropietarios->patchEntity($copropietario, $this->request->getData());
            if ($this->Copropietarios->save($copropietario)) {
                $this->Flash->success(__('Copropietario registrado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar el copropietario. Revise los datos ingresados.'));
        }
        $this->set(compact('copropietario'));

        return null;
    }

    /** Updates a co-owner record. */
    public function edit(int $id): ?Response
    {
        $copropietario = $this->Copropietarios->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $copropietario = $this->Copropietarios->patchEntity($copropietario, $this->request->getData());
            if ($this->Copropietarios->save($copropietario)) {
                $this->Flash->success(__('Copropietario actualizado correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar el copropietario. Revise los datos ingresados.'));
        }
        $this->set(compact('copropietario'));

        return null;
    }

    /** Deletes a co-owner when it has no housing associations. */
    public function delete(int $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->fetchTable('ViviendaCopropietarios')->exists(['copropietario_id' => $id])) {
            $this->Flash->error(__('No se puede eliminar el copropietario porque tiene viviendas asociadas.'));

            return $this->redirect(['action' => 'index']);
        }

        if ($this->Copropietarios->delete($this->Copropietarios->get($id))) {
            $this->Flash->success(__('Copropietario eliminado correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo eliminar el copropietario.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
