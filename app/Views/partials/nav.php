<?php
/**
 * Navegación principal: anclas dentro del mismo scroll, con scroll-spy.
 *
 * @var array<string, mixed> $profile
 * @var string $anchorPrefix  vacío en la landing; url('/') fuera de ella (404)
 */

$anchorPrefix = $anchorPrefix ?? '';

$items = [
    '#inicio'          => 'Inicio',
    '#sobre-mi'        => 'Sobre mí',
    '#proyectos'       => 'Proyectos',
    '#servicios'       => 'Servicios',
    '#certificaciones' => 'Certificaciones',
    '#experiencia'     => 'Experiencia',
];
?>
<header class="site-nav" data-nav>
    <span class="site-nav__progress" data-scroll-progress aria-hidden="true"></span>

    <div class="container site-nav__inner">
        <a class="site-nav__brand" href="<?= e($anchorPrefix) ?>#inicio">
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
                <span class="site-nav__role meta">Data Engineering</span>
            </span>
        </a>

        <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="nav-menu" data-nav-toggle>
            <span class="site-nav__toggle-bars" aria-hidden="true"></span>
            <span class="visually-hidden">Abrir menú</span>
        </button>

        <nav class="site-nav__menu" id="nav-menu" aria-label="Navegación principal">
            <ul class="site-nav__list">
                <?php foreach ($items as $hash => $label): ?>
                    <li>
                        <a class="site-nav__link" href="<?= e($anchorPrefix . $hash) ?>" data-spy-link><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // También participa del scroll-spy: cierra la lista de secciones. ?>
            <a class="cta cta--secondary cta--sm site-nav__cta" href="<?= e($anchorPrefix) ?>#contacto" data-spy-link>Contáctame</a>
        </nav>
    </div>
</header>
