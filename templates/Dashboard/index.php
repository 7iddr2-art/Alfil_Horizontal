<?php
$this->assign('title', __('Dashboard'));
$username = $identity ? $identity->get('username') : '';
?>
<section class="dashboard-header">
    <div>
        <p class="dashboard-kicker">ALFIL HORIZONTAL</p>
        <h1><?= h(__('Panel principal')) ?></h1>
        <p class="dashboard-lead"><?= h(sprintf(__('Bienvenido, %s. Este es el resumen de tu proyecto.'), $username)) ?></p>
    </div>
    <div class="dashboard-status"><span></span> Sesión activa</div>
</section>

<section class="dashboard-grid" aria-label="Resumen del sistema">
    <article class="dashboard-card dashboard-card-cyan">
        <span class="dashboard-card-label">Usuarios registrados</span>
        <strong><?= h((string)$usersCount) ?></strong>
        <span class="dashboard-card-note">Cuentas disponibles</span>
    </article>
    <article class="dashboard-card dashboard-card-magenta">
        <span class="dashboard-card-label">Roles</span>
        <strong><?= h((string)$rolesCount) ?></strong>
        <span class="dashboard-card-note">Niveles de acceso</span>
    </article>
    <article class="dashboard-card dashboard-card-navy">
        <span class="dashboard-card-label">Perfiles</span>
        <strong><?= h((string)$profilesCount) ?></strong>
        <span class="dashboard-card-note">Perfiles configurados</span>
    </article>
</section>

<section class="dashboard-welcome">
    <div>
        <p class="dashboard-kicker">CONTROL DE ACCESO</p>
        <h2>Todo listo para trabajar</h2>
        <p>Tu cuenta tiene permisos de super administrador y puede gestionar la configuración completa del sistema.</p>
    </div>
</section>

<nav class="dashboard-links" aria-label="Administración">
    <a href="<?= $this->Url->build('/users') ?>"><strong>Usuarios</strong><span>Registrar y administrar cuentas</span></a>
    <a href="<?= $this->Url->build('/roles') ?>"><strong>Roles</strong><span>Configurar niveles de acceso</span></a>
    <a href="<?= $this->Url->build('/profiles') ?>"><strong>Perfiles</strong><span>Gestionar perfiles del sistema</span></a>
    <a href="<?= $this->Url->build('/access-audits') ?>"><strong>Auditoría</strong><span>Consultar accesos, sesiones e historial</span></a>
</nav>