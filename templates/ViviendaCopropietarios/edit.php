<?php $this->assign('title', __('Editar asociación')); ?>
<div class="content admin-page"><div class="page-heading"><div><p class="dashboard-kicker">PROPIEDAD HORIZONTAL</p><h1>Editar asociación</h1></div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a></div>
<?= $this->Form->create($asociacion) ?><?= $this->element('ViviendaCopropietarios/form', compact('asociacion', 'viviendas', 'copropietarios')) ?><?= $this->Form->button('Guardar cambios') ?><?= $this->Form->end() ?></div>
