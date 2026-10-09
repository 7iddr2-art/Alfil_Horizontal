<?php $this->assign('title', __('Editar usuario')); ?>
<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Editar usuario</h1>
        </div><a href="<?= $this->Url->build(['action' => 'index']) ?>">Volver</a>
    </div>
    <?= $this->Form->create($user) ?><?= $this->Form->control('username', ['label' => 'Usuario']) ?><?= $this->Form->control('password', ['label' => 'Nueva contraseña', 'required' => false, 'value' => '']) ?><?= $this->Form->control('role_id', ['label' => 'Rol', 'options' => $roles]) ?><?= $this->Form->control('profile_id', ['label' => 'Perfil', 'options' => $profiles]) ?><?= $this->Form->control('active', ['label' => 'Usuario activo']) ?><?= $this->Form->button('Guardar cambios') ?><?= $this->Form->end() ?>
</div>