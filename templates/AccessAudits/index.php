<?php
$this->Html->css('access_audits', ['block' => true]);
$this->Html->script('access_audits', ['block' => true, 'defer' => true]);
$this->assign('title', __('Auditoría de accesos'));
?>
<div class="content admin-page">
    <div class="page-heading access-audits-heading">
        <div>
            <p class="dashboard-kicker">ADMINISTRACION</p>
            <h1>Auditoría de accesos</h1>
        </div>
        <div class="access-audits-actions">
            <button class="button access-audits-toggle" type="button" data-audit-cleanup-toggle aria-controls="access-audits-cleanup" aria-expanded="false">Mostrar limpieza de auditoría</button>
            <a class="button" href="<?= $this->Url->build(['action' => 'exportCsv'] + $this->request->getQuery()) ?>">Exportar CSV</a>
        </div>
    </div>

    <div class="panel access-audits-panel access-audits-filter-panel">
        <?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query']]); ?>
        <div class="access-audits-filter-grid">
            <div>
                <?= $this->Form->control('user', ['label' => 'Usuario', 'value' => $filters['user'] ?? '', 'placeholder' => 'Buscar usuario']); ?>
            </div>
            <div>
                <?= $this->Form->control('session', ['label' => 'Sesión', 'value' => $filters['session'] ?? '', 'placeholder' => 'ID de sesión']); ?>
            </div>
            <div>
                <?= $this->Form->control('status', [
                    'type' => 'select',
                    'label' => 'Estado',
                    'value' => $filters['status'] ?? '',
                    'empty' => 'Todos',
                    'options' => [
                        'connected' => 'Conectado',
                        'disconnected' => 'Desconectado',
                        'missing_logout' => 'Cierre no registrado',
                    ],
                ]); ?>
            </div>
            <div>
                <?= $this->Form->control('start_date', ['type' => 'date', 'label' => 'Desde', 'value' => $filters['start_date'] ?? '']); ?>
            </div>
            <div>
                <?= $this->Form->control('end_date', ['type' => 'date', 'label' => 'Hasta', 'value' => $filters['end_date'] ?? '']); ?>
            </div>
            <div>
                <?= $this->Form->button('Buscar', ['type' => 'submit']); ?>
                <a class="button button-clear access-audits-clear" href="<?= $this->Url->build(['action' => 'index']) ?>">Limpiar</a>
            </div>
        </div>
        <?= $this->Form->end(); ?>
    </div>

    <div class="panel access-audits-panel access-audits-cleanup" id="access-audits-cleanup" hidden>
        <h2 class="access-audits-cleanup-title">Limpieza de auditoría</h2>
        <div class="access-audits-cleanup-grid">
            <section>
                <h3>Por rango de fechas</h3>
                <?= $this->Form->create(null, ['url' => ['action' => 'cleanup'], 'type' => 'post']); ?>
                <?= $this->Form->hidden('cleanup_mode', ['value' => 'range']); ?>
                <div class="access-audits-date-grid">
                    <?= $this->Form->control('cleanup_start_date', ['type' => 'date', 'label' => 'Desde', 'required' => true, 'value' => '']); ?>
                    <?= $this->Form->control('cleanup_end_date', ['type' => 'date', 'label' => 'Hasta', 'required' => true, 'value' => '']); ?>
                </div>
                <?= $this->Form->button('Eliminar registros del rango', ['type' => 'submit']); ?>
                <?= $this->Form->end(); ?>
            </section>
            <section>
                <h3>Limpieza total</h3>
                <?= $this->Form->create(null, ['url' => ['action' => 'cleanup'], 'type' => 'post']); ?>
                <?= $this->Form->hidden('cleanup_mode', ['value' => 'all']); ?>
                <label class="access-audits-confirm">
                    <input type="checkbox" name="confirm_all" value="1" required>
                    Confirmo eliminar todos los registros inactivos. Las sesiones conectadas se conservan.
                </label>
                <?= $this->Form->button('Eliminar todos los registros inactivos', [
                    'type' => 'submit',
                    'class' => 'access-audits-danger-button',
                ]); ?>
                <?= $this->Form->end(); ?>
            </section>
        </div>
    </div>

    <div class="table-responsive access-audits-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Sesión</th>
                    <th>Estado</th>
                    <th>IP</th>
                    <th>Conexión</th>
                    <th>Desconexión</th>
                    <th>Duración</th>
                    <th>Última actividad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accessAudits as $audit) : ?>
                    <tr>
                        <td><?= h($audit->username ?? $audit->user->username ?? 'N/A') ?></td>
                        <td><?= h($audit->session_id ?? 'N/A') ?></td>
                        <td>
                            <?php if ($audit->status === 'connected') : ?>
                                <span class="access-audits-status access-audits-status--connected">Conectado</span>
                            <?php elseif ($audit->status === 'disconnected') : ?>
                                <span class="access-audits-status access-audits-status--disconnected">Desconectado</span>
                            <?php elseif ($audit->status === 'missing_logout') : ?>
                                <span class="access-audits-status access-audits-status--missing-logout">Cierre no registrado</span>
                            <?php else : ?>
                                <span class="access-audits-status access-audits-status--unknown">Desconocido</span>
                            <?php endif; ?>
                        </td>
                        <td><?= h($audit->ip_address ?? 'N/A') ?></td>
                        <td><?= $audit->login_at ? h($audit->login_at->format('d/m/Y H:i:s')) : 'N/A' ?></td>
                        <td><?= $audit->logout_at ? h($audit->logout_at->format('d/m/Y H:i:s')) : 'N/A' ?></td>
                        <td><?= $audit->duration_seconds !== null ? h((string)$audit->duration_seconds . ' s') : 'N/A' ?></td>
                        <td><?= $audit->last_activity_at ? h($audit->last_activity_at->format('d/m/Y H:i:s')) : 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($accessAudits->isEmpty()) : ?>
                    <tr>
                        <td class="access-audits-empty" colspan="8">No hay registros de auditoría para los filtros seleccionados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination access-audits-pagination">
        <div>
            <?= $this->Paginator->counter('Página {{page}} de {{pages}} · {{current}} registros de {{count}}'); ?>
        </div>
        <div class="access-audits-pagination-controls">
            <?= $this->Paginator->prev('Anterior'); ?>
            <?= $this->Paginator->numbers(); ?>
            <?= $this->Paginator->next('Siguiente'); ?>
        </div>
    </div>
</div>
