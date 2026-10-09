<?php $this->assign('title', __('Editar copropietario')); ?>
<div class="content admin-page"><div class="page-heading"><div><p class="dashboard-kicker">PROPIEDAD HORIZONTAL</p><h1>Editar copropietario</h1></div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a></div>
<?= $this->Form->create($copropietario) ?><?= $this->element('Copropietarios/form', compact('copropietario')) ?><?= $this->Form->button('Guardar cambios') ?><?= $this->Form->end() ?></div>
