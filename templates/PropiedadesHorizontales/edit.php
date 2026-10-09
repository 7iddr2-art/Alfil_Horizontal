<?php $this->assign('title', 'Editar empresa'); ?>
<div class="content admin-page">
    <h1>Editar empresa</h1>
    <?= $this->Form->create($propiedad) ?><?= $this->element('PropiedadesHorizontales/form') ?><?= $this->Form->button('Guardar cambios') ?><?= $this->Form->end() ?>
</div>