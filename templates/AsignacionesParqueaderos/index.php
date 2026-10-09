<div class="content admin-page">
    <h1>Asignaciones de parqueadero</h1><a class="button" href="<?= $this->Url->build(['action' => 'add']) ?>">Nueva
        asignación</a>
    <table>
        <tr>
            <th>Parqueadero</th>
            <th>Copropietario</th>
            <th>Vehículo</th>
            <th>Mensualidad</th>
            <th>Vigencia</th>
            <th></th>
        </tr><?php foreach ($items as $item): ?>
            <tr>
                <td><?= h($item->parqueadero->code) ?></td>
                <td><?= h($item->copropietario->document_number) ?></td>
                <td><?= h($item->vehicle_plate) ?></td>
                <td><?= h($item->monthly_fee) ?></td>
                <td><?= h($item->start_date) ?> - <?= h($item->end_date) ?></td>
                <td><a href="<?= $this->Url->build(['action' => 'edit', $item->id]) ?>">Editar</a></td>
            </tr><?php endforeach; ?>
    </table>
</div>