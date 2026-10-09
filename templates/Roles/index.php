<?php $this->assign('title', __('Roles')); ?>
<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Roles</h1>
        </div><a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Nuevo rol</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Super administrador</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody><?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= h($role->name) ?></td>
                        <td><?= h($role->description) ?></td>
                        <td><?= $role->is_super_admin ? 'Sí' : 'No' ?></td>
                        <td class="actions"><a
                                href="<?= $this->Url->build(['action' => 'edit', $role->id]) ?>">Editar</a><?= $this->Form->postLink('Eliminar', ['action' => 'delete', $role->id], ['confirm' => '¿Eliminar este rol?']) ?>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>