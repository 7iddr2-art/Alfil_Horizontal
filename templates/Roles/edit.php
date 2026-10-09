<?php $this->assign('title', __('Editar rol')); ?>
<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Editar rol</h1>
        </div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a>
    </div>
    <?= $this->Form->create($role) ?><?= $this->Form->control('name', ['label' => 'Nombre']) ?><?= $this->Form->control('description', ['label' => 'Descripción']) ?><?= $this->Form->control('is_super_admin', ['label' => 'Super administrador']) ?><?= $this->Form->button('Guardar cambios') ?><?= $this->Form->end() ?>
</div>