<?php $this->assign('title', 'Nueva empresa'); ?>
<div class="content admin-page">
    <h1>Nueva empresa</h1>
    <?= $this->Form->create($propiedad) ?><?= $this->element('PropiedadesHorizontales/form') ?><?= $this->Form->button('Guardar') ?><?= $this->Form->end() ?>
</div>