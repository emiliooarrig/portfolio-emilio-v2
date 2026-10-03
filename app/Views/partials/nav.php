<?php
/**
 * Navegación principal: anclas dentro del mismo scroll, con scroll-spy.
 *
 * @var array<string, mixed> $profile
 * @var string $anchorPrefix  vacío en la landing; url('/') fuera de ella (404)
 */

$anchorPrefix = $anchorPrefix ?? '';

$items = [
    '#proyectos'       => 'Proyectos',
    '#como-trabajo'    => 'Cómo trabajo',
    '#experiencia'     => 'Experiencia',
    '#sobre-mi'        => 'Sobre mí',
    '#certificaciones' => 'Certificaciones',
];
?>
<header class="site-nav" data-nav>
    <div class="container site-nav__inner">
        <a class="site-nav__brand" href="<?= e($anchorPrefix) ?>#inicio"><?= e($profile['full_name']) ?></a>

        <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="nav-menu" data-nav-toggle>
            <span class="site-nav__toggle-open">Menú</span>
            <span class="site-nav__toggle-close">Cerrar</span>
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
            <a class="cta cta--secondary cta--sm site-nav__cta" href="<?= e($anchorPrefix) ?>#contacto" data-spy-link>Contacto</a>
        </nav>
    </div>
</header>
