<?php
/**
 * Sección Servicios — carrusel horizontal.
 *
 * Es la sección que lee alguien que no es de sistemas: aquí no entra jerga
 * ni nombres de herramientas. El stack vive en "Sobre mí" y el detalle
 * técnico, en el modal de cada proyecto.
 *
 * El carrusel funciona sin JS: el riel es un contenedor con scroll y
 * scroll-snap, así que se puede arrastrar o deslizar igual. El JS sólo
 * añade las flechas, los puntos y el teclado.
 *
 * @var array<int, array<string, mixed>> $services
 */

/** Íconos inline: la clave viene de services.icon. */
$icons = [
    'browser' => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 9h18M7 6.5h.01M10 6.5h.01"></path>',
    'layers'  => '<path d="M12 3l9 5-9 5-9-5 9-5Z"></path><path d="M3 13l9 5 9-5"></path>',
    'bolt'    => '<path d="M13 3L5 14h6l-1 7 8-11h-6l1-7Z"></path>',
    'boxes'   => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
    'chart'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"></path>',
    'shield'  => '<path d="M12 3l8 3v6c0 4.5-3.2 7.9-8 9-4.8-1.1-8-4.5-8-9V6l8-3Z"></path><path d="M9 12l2 2 4-4"></path>',
    'spark'   => '<path d="M12 3v6M12 15v6M3 12h6M15 12h6"></path>',
];

$total = count($services);
?>
<section class="section" id="servicios">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'En qué te puedo ayudar',
            'title'   => 'Servicios',
            'lead'    => 'No necesitas saber cómo funciona por dentro. Dime qué te está costando trabajo y yo me encargo de la parte técnica.',
            'meta'    => $total > 0 ? $total . ' formas de empezar' : null,
        ]) ?>

        <?php if ($services === []): ?>
            <p class="empty-state">Todavía no hay servicios publicados.</p>
        <?php else: ?>
            <div class="carousel reveal"
                 data-carousel
                 role="group"
                 aria-roledescription="carrusel"
                 aria-label="Servicios que ofrezco">

                <ul class="carousel__track"
                    data-carousel-track
                    tabindex="0"
                    aria-label="<?= $total ?> servicios, desliza para ver más">

                    <?php foreach ($services as $index => $service): ?>
                        <li class="carousel__slide">
                            <article class="service-card bento-card--hoverable<?= ! empty($service['is_featured']) ? ' service-card--featured' : '' ?>"
                                     aria-roledescription="tarjeta"
                                     aria-label="<?= $index + 1 ?> de <?= $total ?>">

                                <div class="service-card__head">
                                    <span class="service-card__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                                             stroke="currentColor" stroke-width="1.6"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <?= $icons[$service['icon']] ?? $icons['spark'] ?>
                                        </svg>
                                    </span>

                                    <?php if (! empty($service['timeframe'])): ?>
                                        <span class="service-card__timeframe meta"><?= e($service['timeframe']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="service-card__body">
                                    <h3 class="service-card__title"><?= e($service['title']) ?></h3>

                                    <?php if (! empty($service['tagline'])): ?>
                                        <p class="service-card__tagline"><?= e($service['tagline']) ?></p>
                                    <?php endif; ?>

                                    <p class="service-card__text"><?= e($service['description']) ?></p>

                                    <?php if (! empty($service['deliverables'])): ?>
                                        <ul class="service-card__list">
                                            <?php foreach ($service['deliverables'] as $item): ?>
                                                <li><?= e($item) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>

                                <?php if (! empty($service['outcome'])): ?>
                                    <p class="service-card__outcome"><?= e($service['outcome']) ?></p>
                                <?php endif; ?>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php // Controles: los pinta el servidor y el JS los activa. Sin JS quedan ocultos. ?>
                <div class="carousel__controls" data-carousel-controls hidden>
                    <button class="carousel__arrow" type="button" data-carousel-prev aria-label="Ver servicios anteriores">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M15 6l-6 6 6 6"></path>
                        </svg>
                    </button>

                    <div class="carousel__dots" data-carousel-dots role="tablist" aria-label="Ir a un servicio">
                        <?php foreach ($services as $index => $service): ?>
                            <button class="carousel__dot"
                                    type="button"
                                    role="tab"
                                    data-carousel-dot="<?= $index ?>"
                                    aria-label="<?= e($service['title']) ?>"
                                    aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel__arrow" type="button" data-carousel-next aria-label="Ver más servicios">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M9 6l6 6-6 6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?= partial('cta-block', [
            'title'     => '¿No sabes en cuál de todos cae lo tuyo?',
            'text'      => 'Cuéntamelo como se lo contarías a un amigo. Yo te digo si puedo ayudarte y cómo.',
            'primary'   => ['label' => 'Cuéntame tu caso', 'href' => '#contacto', 'icon' => '→'],
            'secondary' => ['label' => 'Ver trabajos anteriores', 'href' => '#proyectos'],
            'variant'   => 'inline',
        ]) ?>
    </div>
</section>
