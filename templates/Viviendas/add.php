<?php $this->assign('title', __('Nueva vivienda')); ?>
<div class="content admin-page"><div class="page-heading"><div><p class="dashboard-kicker">PROPIEDAD HORIZONTAL</p><h1>Nueva vivienda</h1></div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a></div>
<?= $this->Form->create($vivienda) ?><?= $this->element('Viviendas/form', compact('vivienda')) ?><?= $this->Form->button('Guardar vivienda') ?><?= $this->Form->end() ?></div>
