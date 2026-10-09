<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class PolizasAseguramientoController extends AppController
{
    /** Lists insurance policies. */
    public function index(): ?Response
    {
        $this->set('polizas', $this->PolizasAseguramiento->find()->contain(['PropiedadesHorizontales'])->orderBy(['end_date' => 'ASC'])->all());
        return null;
    }

    /** Creates an insurance policy. */
    public function add(): ?Response
    {
        $poliza = $this->PolizasAseguramiento->newEmptyEntity();
        if ($this->request->is('post')) {
            $poliza = $this->PolizasAseguramiento->patchEntity($poliza, $this->request->getData());
            if ($this->PolizasAseguramiento->save($poliza)) {
                $this->Flash->success(__('Póliza registrada correctamente.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar la póliza.'));
        }
        $this->set(compact('poliza'));
        $this->setProperties();
        return null;
    }

    /** Updates an insurance policy. */
    public function edit(int $id): ?Response
    {
        $poliza = $this->PolizasAseguramiento->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $poliza = $this->PolizasAseguramiento->patchEntity($poliza, $this->request->getData());
            if ($this->PolizasAseguramiento->save($poliza)) {
                $this->Flash->success(__('Póliza actualizada correctamente.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar la póliza.'));
        }
        $this->set(compact('poliza'));
        $this->setProperties();
        return null;
    }

    /** Loads horizontal-property options. */
    private function setProperties(): void
    {
        $this->set('propiedades', $this->fetchTable('PropiedadesHorizontales')->find('list')->all());
    }
}
