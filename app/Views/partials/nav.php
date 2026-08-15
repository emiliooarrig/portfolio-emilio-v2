<?php
/**
 * Navegación principal.
 *
 * @var string $activePath
 * @var array<string, mixed> $profile
 */

$items = [
    '/'                => 'Inicio',
    '/sobre-mi'        => 'Sobre mí',
    '/proyectos'       => 'Proyectos',
    '/experiencia'     => 'Experiencia',
    '/certificaciones' => 'Certificaciones',
];
?>
<header class="site-nav" data-nav>
    <div class="container site-nav__inner">
        <a class="site-nav__brand" href="<?= url('/') ?>">
            <span class="site-nav__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                    <path d="M14 17.5h7M17.5 14v7"></path>
                </svg>
            </span>
            <span class="site-nav__brand-text">
                <span class="site-nav__name"><?= e($profile['full_name']) ?></span>
                <span class="site-nav__role mono">Data Engineering</span>
            </span>
        </a>

        <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="nav-menu" data-nav-toggle>
            <span class="site-nav__toggle-bars" aria-hidden="true"></span>
            <span class="visually-hidden">Abrir menú</span>
        </button>

        <nav class="site-nav__menu" id="nav-menu" aria-label="Navegación principal">
            <ul class="site-nav__list">
                <?php foreach ($items as $path => $label): ?>
                    <?php $active = is_active($activePath, $path); ?>
                    <li>
                        <a class="site-nav__link<?= $active ? ' is-active' : '' ?>"
                           href="<?= url($path) ?>"
                           <?= $active ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <a class="cta cta--secondary cta--sm site-nav__cta" href="<?= url('/contacto') ?>">Contáctame</a>
        </nav>
    </div>
</header>
