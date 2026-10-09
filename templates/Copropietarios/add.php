<?php $this->assign('title', __('Nuevo copropietario')); ?>
<div class="content admin-page"><div class="page-heading"><div><p class="dashboard-kicker">PROPIEDAD HORIZONTAL</p><h1>Nuevo copropietario</h1></div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a></div>
<?= $this->Form->create($copropietario) ?><?= $this->element('Copropietarios/form', compact('copropietario')) ?><?= $this->Form->button('Guardar copropietario') ?><?= $this->Form->end() ?></div>
