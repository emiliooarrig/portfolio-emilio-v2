<?php
/**
 * Navegación del panel.
 *
 * Aquí vive la lista de pantallas: añadir una es añadir su ruta en
 * `config/routes.php`, su acción en `AdminController` y una entrada en este
 * array. No hay una segunda lista en ningún otro sitio.
 *
 * @var array<string, mixed>      $profile
 * @var array<string, mixed>|null $admin
 * @var string $token
 * @var string $activePath
 */

$groups = [
    [
        'label' => 'General',
        'items' => [
            ['path' => '/admin', 'label' => 'Resumen', 'icon' => 'grid'],
        ],
    ],
    [
        // Cada una es una sección del scroll de la landing.
        'label' => 'Contenido',
        'items' => [
            ['path' => '/admin/proyectos',       'label' => 'Proyectos',      'icon' => 'layers'],
            ['path' => '/admin/servicios',       'label' => 'Servicios',      'icon' => 'spark'],
            ['path' => '/admin/certificaciones', 'label' => 'Certificaciones', 'icon' => 'award'],
            ['path' => '/admin/experiencia',     'label' => 'Experiencia',    'icon' => 'case'],
            ['path' => '/admin/tecnologias',     'label' => 'Stack',          'icon' => 'stack'],
            ['path' => '/admin/perfil',          'label' => 'Perfil',         'icon' => 'user'],
        ],
    ],
    [
        'label' => 'Entrada',
        'items' => [
            ['path' => '/admin/mensajes', 'label' => 'Mensajes', 'icon' => 'mail'],
        ],
    ],
];

/** Trazos de cada icono. Uno solo por pantalla, en el mismo lienzo de 24. */
$icon = static function (string $key): string {
    return match ($key) {
        'layers' => '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="M3 13l9 5 9-5"/>',
        'spark'  => '<path d="M12 3.5l2.2 5.5 5.8 1.5-5.8 1.5L12 17.5 9.8 12 4 10.5 9.8 9 12 3.5Z"/>',
        'award'  => '<circle cx="12" cy="9" r="5.5"/><path d="M8.5 13.4 7 21l5-2.6L17 21l-1.5-7.6"/>',
        'case'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/>',
        'stack'  => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.66 3.58 3 8 3s8-1.34 8-3V6"/><path d="M4 12c0 1.66 3.58 3 8 3s8-1.34 8-3"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.58-6 8-6s8 2 8 6"/>',
        'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.6 7 8.4 6 8.4-6"/>',
        default  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    };
};
?>
<aside class="admin__sidebar">

    <a class="admin__brand" href="<?= url('/admin') ?>">
        <span class="admin__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                <path d="M14 17.5h7M17.5 14v7"></path>
            </svg>
        </span>
        <span class="admin__brand-text">
            <strong class="admin__brand-name"><?= e($profile['full_name']) ?></strong>
            <span class="admin__brand-role meta">Panel</span>
        </span>
    </a>

    <nav class="admin__nav" aria-label="Secciones del panel">
        <?php foreach ($groups as $group): ?>
            <p class="admin__nav-label meta"><?= e($group['label']) ?></p>
            <ul>
                <?php foreach ($group['items'] as $item): ?>
                    <?php $isActive = $activePath === $item['path']; ?>
                    <li>
                        <a class="admin__link<?= $isActive ? ' is-active' : '' ?>"
                           href="<?= url($item['path']) ?>"
                           <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <svg class="admin__link-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true"><?= $icon($item['icon']) ?></svg>
                            <?= e($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </nav>

    <div class="admin__footer">
        <?php if (is_array($admin)): ?>
            <p class="admin__who">
                <span class="meta"><?= e($admin['username']) ?></span>
                <span class="admin__who-since">dentro desde <?= e(date('H:i', (int) $admin['login_at'])) ?></span>
            </p>
        <?php endif; ?>

        <a class="admin__out" href="<?= url('/') ?>">Ver el sitio</a>

        <?php // Salir es POST y con token: un GET lo dispararía cualquier <img> ajena. ?>
        <form method="post" action="<?= url('/admin/logout') ?>">
            <input type="hidden" name="_token" value="<?= e($token) ?>">
            <button class="admin__logout" type="submit">Cerrar sesión</button>
        </form>
    </div>

</aside>
