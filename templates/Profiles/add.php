<?php $this->assign('title', __('Nuevo perfil')); ?>
<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Nuevo perfil</h1>
        </div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a>
    </div>
    <?= $this->Form->create($profile) ?><?= $this->Form->control('name', ['label' => 'Nombre']) ?><?= $this->Form->control('description', ['label' => 'Descripción']) ?><?= $this->Form->button('Guardar perfil') ?><?= $this->Form->end() ?>
</div>