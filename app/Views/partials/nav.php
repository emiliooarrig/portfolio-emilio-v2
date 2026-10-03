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
                        <a class="site-nav__link" href="<?= e($anchorPrefix . $hash) ?>" data-spy-link><?= roll_text($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // Sin JS no hace nada, así que sólo se muestra con `html.js`.
                  // `theme.js` mantiene al día aria-pressed y la etiqueta. ?>
            <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false" aria-label="Activar modo oscuro">
                <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 14.6A8.5 8.5 0 0 1 9.4 4a8.5 8.5 0 1 0 10.6 10.6Z"></path>
                </svg>
                <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4.2"></circle>
                    <path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M5.3 18.7l1.6-1.6M17.1 6.9l1.6-1.6"></path>
                </svg>
            </button>

            <?php // También participa del scroll-spy: cierra la lista de secciones. ?>
            <a class="cta cta--secondary cta--sm site-nav__cta" href="<?= e($anchorPrefix) ?>#contacto" data-spy-link><?= roll_text('Contacto') ?></a>
        </nav>
    </div>
</header>
