<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?php $identity = $this->getRequest()->getAttribute('identity'); ?>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a class="brand-link" href="<?= $this->Url->build('/') ?>" aria-label="Alfil Horizontal">
                <span class="brand-wordmark"><svg class="brand-icon" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 256 256"
                    width="32"
                    height="32"
                    aria-hidden="true"
                    focusable="false">

                <defs>
                    <linearGradient id="alfilGradient"
                                    x1="0%" y1="0%"
                                    x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00d9ff"/>
                    <stop offset="50%" stop-color="#0879d1"/>
                    <stop offset="100%" stop-color="#ff006b"/>
                    </linearGradient>

                    <linearGradient id="baseGradient"
                                    x1="0%" y1="0%"
                                    x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#00d9ff"/>
                    <stop offset="100%" stop-color="#ff006b"/>
                    </linearGradient>
                    <clipPath id="alfilHeadClip">
                        <path d="M128 4 C106 4 91 22 91 45 C91 63 101 77 115 86 L99 120 C84 139 76 154 76 171 C76 185 87 195 103 198 H153 C169 195 180 185 180 171 C180 154 172 139 157 120 L141 86 C155 77 165 63 165 45 C165 22 150 4 128 4 Z"/>
                    </clipPath>
                </defs>

                <!-- Cabeza y cuerpo -->
                <path
                    d="
                    M128 4
                    C106 4 91 22 91 45
                    C91 63 101 77 115 86
                    L99 120
                    C84 139 76 154 76 171
                    C76 185 87 195 103 198
                    H153
                    C169 195 180 185 180 171
                    C180 154 172 139 157 120
                    L141 86
                    C155 77 165 63 165 45
                    C165 22 150 4 128 4
                    Z"
                    fill="url(#alfilGradient)"/>

                <!-- Abertura diagonal característica del alfil -->
                <path
                    d="M108 23 L148 65"
                    clip-path="url(#alfilHeadClip)"
                    fill="none"
                    stroke="#ffffff"
                    stroke-width="9"
                    stroke-linecap="round"/>

                <!-- Transición hacia la base -->
                <path
                    d="
                    M88 196
                    C95 204 99 210 98 216
                    H158
                    C157 210 161 204 168 196
                    Z"
                    fill="url(#alfilGradient)"/>

                <!-- Base superior -->
                <path
                    d="
                    M74 212
                    H182
                    C190 212 195 217 195 223
                    C195 229 190 234 182 234
                    H74
                    C66 234 61 229 61 223
                    C61 217 66 212 74 212
                    Z"
                    fill="url(#baseGradient)"/>

                <!-- Base inferior -->
                <path
                    d="
                    M48 230
                    H208
                    C216 230 222 236 222 243
                    C222 250 216 255 208 255
                    H48
                    C40 255 34 250 34 243
                    C34 236 40 230 48 230
                    Z"
                    fill="url(#alfilGradient)"/>

                </svg><span class="brand-name">Alfil</span></span><span class="brand-horizontal"> - Horizontal</span>
            </a>
        </div>
        <div class="top-nav-links">
            <?php if ($identity) : ?>
                <span class="top-nav-user"><?= h($identity->get('username')) ?></span>
                <a href="<?= $this->Url->build('/users/logout') ?>">Cerrar sesión</a>
            <?php else : ?>
                <a href="<?= $this->Url->build('/users/login') ?>">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main">
        <div class="app-shell">
            <?php if ($identity) : ?>
                <?php $currentPath = $this->getRequest()->getUri()->getPath(); ?>
                <aside class="side-nav" aria-label="Menú principal">
                    <details class="side-nav-menu" open>
                        <summary>Menú principal</summary>
                        <div class="side-nav-list">
                            <details class="side-nav-group" <?= $currentPath === '/' ? 'open' : '' ?>>
                                <summary>Inicio</summary>
                                <a class="side-nav-item<?= $currentPath === '/' ? ' active' : '' ?>" href="<?= $this->Url->build('/') ?>">Dashboard</a>
                            </details>
                            <details class="side-nav-group" <?= str_starts_with($currentPath, '/users') || str_starts_with($currentPath, '/roles') || str_starts_with($currentPath, '/profiles') || str_starts_with($currentPath, '/access-audits') ? 'open' : '' ?>>
                                <summary>Administración APP</summary>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/users') ? ' active' : '' ?>" href="<?= $this->Url->build('/users') ?>">Usuarios</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/roles') ? ' active' : '' ?>" href="<?= $this->Url->build('/roles') ?>">Roles</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/profiles') ? ' active' : '' ?>" href="<?= $this->Url->build('/profiles') ?>">Perfiles</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/access-audits') ? ' active' : '' ?>" href="<?= $this->Url->build('/access-audits') ?>">Auditoría</a>
                            </details>
                            <details class="side-nav-group" <?= str_starts_with($currentPath, '/copropietarios') || str_starts_with($currentPath, '/viviendas') || str_starts_with($currentPath, '/vivienda-copropietarios') ? 'open' : '' ?>>
                                <summary>Propiedad horizontal</summary>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/copropietarios') ? ' active' : '' ?>" href="<?= $this->Url->build('/copropietarios') ?>">Copropietarios</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/viviendas') ? ' active' : '' ?>" href="<?= $this->Url->build('/viviendas') ?>">Viviendas</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/vivienda-copropietarios') ? ' active' : '' ?>" href="<?= $this->Url->build('/vivienda-copropietarios') ?>">Viviendas y copropietarios</a>
                            </details>
                            <details class="side-nav-group" <?= str_starts_with($currentPath, '/propiedades-horizontales') || str_starts_with($currentPath, '/personal-administraciones') || str_starts_with($currentPath, '/polizas-aseguramiento') ? 'open' : '' ?>>
                                <summary>Gestión de la copropiedad</summary>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/propiedades-horizontales') ? ' active' : '' ?>" href="<?= $this->Url->build('/propiedades-horizontales') ?>">Empresa</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/personal-administraciones') ? ' active' : '' ?>" href="<?= $this->Url->build('/personal-administraciones') ?>">Personal administrativo</a>
                                <a class="side-nav-item<?= str_starts_with($currentPath, '/polizas-aseguramiento') ? ' active' : '' ?>" href="<?= $this->Url->build('/polizas-aseguramiento') ?>">Pólizas</a>
                            </details>
                            <details class="side-nav-group" <?= str_starts_with($currentPath, '/parqueaderos') || str_starts_with($currentPath, '/asignaciones-parqueaderos') || str_starts_with($currentPath, '/pagos-parqueaderos') ? 'open' : '' ?>><summary>Parqueaderos</summary><a class="side-nav-item" href="<?= $this->Url->build('/parqueaderos') ?>">Parqueaderos</a><a class="side-nav-item" href="<?= $this->Url->build('/asignaciones-parqueaderos') ?>">Asignaciones</a><a class="side-nav-item" href="<?= $this->Url->build('/pagos-parqueaderos') ?>">Pagos</a></details>
                            <details class="side-nav-group" <?= str_starts_with($currentPath, '/residentes') ? 'open' : '' ?>><summary>Residentes</summary><a class="side-nav-item<?= str_starts_with($currentPath, '/residentes') ? ' active' : '' ?>" href="<?= $this->Url->build('/residentes') ?>">Administrar residentes</a></details>
                        </div>
                    </details>
                </aside>
            <?php endif; ?>
            <div class="container">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
