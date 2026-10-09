<?php $this->assign('title', __('Usuarios')); ?>

<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Usuarios</h1>
        </div><a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Nuevo usuario</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Perfil</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= h($user->username) ?></td>
                        <td><?= h($user->role->name ?? '') ?></td>
                        <td><?= h($user->profile->name ?? '') ?></td>
                        <td><?= $user->active ? 'Activo' : 'Inactivo' ?></td>
                        <td class="actions"><a
                                href="<?= $this->Url->build(['action' => 'edit', $user->id]) ?>">Editar</a><?= $this->Form->postLink('Eliminar', ['action' => 'delete', $user->id], ['confirm' => '¿Eliminar este usuario?']) ?>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>