<?php
$this->assign('title', __('Inicio de sesión'));
?>
<main class="login-main">
    <div class="login">
        <h1><?= h(__('Iniciar sesión')) ?></h1>
        <?= $this->Form->create(null) ?>
        <?= $this->Form->control('username', ['label' => __('Usuario'), 'required' => true]) ?>
        <?= $this->Form->control('password', ['label' => __('Contraseña'), 'required' => true]) ?>
        <?= $this->Form->button(__('Ingresar')) ?>
        <?= $this->Form->end() ?>
    </div>
</main>
