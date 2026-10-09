<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\I18n\Date;

class PersonalAdministracionesController extends AppController
{
    /** Lists current and historical administration personnel. */
    public function index(): ?Response
    {
        $this->set('personal', $this->PersonalAdministraciones->find()
            ->contain(['PropiedadesHorizontales'])
            ->orderBy(['PersonalAdministraciones.active' => 'DESC', 'PersonalAdministraciones.full_name' => 'ASC'])
            ->all());

        return null;
    }

    /** Creates a personnel record. */
    public function add(): ?Response
    {
        $registro = $this->PersonalAdministraciones->newEmptyEntity();
        if ($this->request->is('post')) {
            $registro = $this->PersonalAdministraciones->patchEntity($registro, $this->request->getData());
            if ($this->PersonalAdministraciones->save($registro)) {
                $this->Flash->success(__('Personal registrado correctamente.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo registrar el personal.'));
        }
        $this->set(compact('registro'));
        $this->setProperties();
        return null;
    }

    /** Updates a personnel record without removing its history. */
    public function edit(int $id): ?Response
    {
        $registro = $this->PersonalAdministraciones->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $registro = $this->PersonalAdministraciones->patchEntity($registro, $this->request->getData());
            if ($this->PersonalAdministraciones->save($registro)) {
                $this->Flash->success(__('Registro de personal actualizado correctamente.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('No se pudo actualizar el registro.'));
        }
        $this->set(compact('registro'));
        $this->setProperties();
        return null;
    }

    /** Ends a personnel relationship while retaining its historical record. */
    public function finish(int $id): Response
    {
        $this->request->allowMethod(['post']);
        $registro = $this->PersonalAdministraciones->get($id);
        $registro->active = false;
        $registro->end_date ??= Date::now();
        $this->PersonalAdministraciones->save($registro);
        $this->Flash->success(__('Vinculación finalizada; el registro histórico se conserva.'));
        return $this->redirect(['action' => 'index']);
    }

    /** Loads horizontal-property options. */
    private function setProperties(): void
    {
        $this->set('propiedades', $this->fetchTable('PropiedadesHorizontales')->find('list')->all());
    }
}
