<?php $this->assign('title', __('Perfiles')); ?>
<div class="content admin-page">
    <div class="page-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Perfiles</h1>
        </div><a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Nuevo perfil</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody><?php foreach ($profiles as $profile): ?>
                    <tr>
                        <td><?= h($profile->name) ?></td>
                        <td><?= h($profile->description) ?></td>
                        <td class="actions"><a
                                href="<?= $this->Url->build(['action' => 'edit', $profile->id]) ?>">Editar</a><?= $this->Form->postLink('Eliminar', ['action' => 'delete', $profile->id], ['confirm' => '¿Eliminar este perfil?']) ?>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>