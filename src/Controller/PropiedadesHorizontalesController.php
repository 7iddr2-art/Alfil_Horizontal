<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class PropiedadesHorizontalesController extends AppController
{
    /** Lists horizontal properties. */
    public function index(): ?Response
    {
        $this->set('propiedades', $this->PropiedadesHorizontales->find()->orderBy(['name' => 'ASC'])->all());

        return null;
    }

    /** Creates a horizontal-property record. */
    public function add(): ?Response
    {
        $propiedad = $this->PropiedadesHorizontales->newEmptyEntity();
        if ($this->request->is('post')) {
            $propiedad = $this->PropiedadesHorizontales->patchEntity($propiedad, $this->request->getData());
            if ($this->PropiedadesHorizontales->save($propiedad)) {
                $this->Flash->success(__('Propiedad horizontal registrada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar la propiedad horizontal.'));
        }
        $this->set(compact('propiedad'));

        return null;
    }

    /** Updates a horizontal-property record. */
    public function edit(int $id): ?Response
    {
        $propiedad = $this->PropiedadesHorizontales->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $propiedad = $this->PropiedadesHorizontales->patchEntity($propiedad, $this->request->getData());
            if ($this->PropiedadesHorizontales->save($propiedad)) {
                $this->Flash->success(__('Propiedad horizontal actualizada correctamente.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar la propiedad horizontal.'));
        }
        $this->set(compact('propiedad'));

        return null;
    }
}
