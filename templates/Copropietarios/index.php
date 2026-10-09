<?php $this->assign('title', __('Copropietarios')); ?>
<div class="content admin-page">
    <div class="page-heading"><div><p class="dashboard-kicker">PROPIEDAD HORIZONTAL</p><h1>Copropietarios</h1></div><a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Nuevo copropietario</a></div>
    <div class="table-responsive"><table><thead><tr><th>Documento</th><th>Nombre completo</th><th>Contacto</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
    <?php foreach ($copropietarios as $copropietario): ?><tr>
        <td><?= h($copropietario->document_type) ?> <?= h($copropietario->document_number) ?></td>
        <td><?= h($copropietario->full_name) ?></td><td><?= h($copropietario->email ?: $copropietario->mobile_phone) ?></td>
        <td><?= $copropietario->active ? 'Activo' : 'Inactivo' ?></td>
        <td class="actions"><a href="<?= $this->Url->build(['action' => 'edit', $copropietario->id]) ?>">Editar</a><?= $this->Form->postLink('Eliminar', ['action' => 'delete', $copropietario->id], ['confirm' => '¿Eliminar este copropietario?']) ?></td>
    </tr><?php endforeach; ?></tbody></table></div>
</div>
