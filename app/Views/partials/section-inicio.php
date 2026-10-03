<?php
/**
 * Sección Inicio — el nombre como interfaz.
 *
 * El nombre, la profesión y la promesa; al lado, el estado (disponible,
 * ubicación, CV) y debajo las dos acciones. El nombre es el único momento
 * de movimiento del sitio: cada palabra se asienta al cargar (main.js).
 *
 * @var array<string, mixed> $profile
 */

// Una palabra por línea; cada una lleva su índice de retardo.
$fullName = trim((string) $profile['full_name']);
$words    = preg_split('/\s+/u', $fullName) ?: [];
?>
<section class="hero" id="inicio">
    <div class="container hero__inner">
        <?php // La firma visual: misma composición siempre para el mismo nombre. ?>
        <div class="hero__art" data-parallax>
            <?= partial('pattern', ['seed' => $fullName, 'variant' => 'hero']) ?>
        </div>

        <h1 class="hero__name" aria-label="<?= e($fullName) ?>" data-hero-name>
            <?php foreach ($words as $index => $word): ?>
                <span class="hero__word" style="--word-index: <?= $index ?>" aria-hidden="true"><?= e($word) ?></span>
            <?php endforeach; ?>
        </h1>

        <p class="hero__role"><?= e($profile['role_title']) ?></p>

        <div class="hero__grid">
            <p class="hero__headline"><?= e($profile['headline']) ?></p>

            <div class="hero__actions">
                <a class="cta cta--primary cta--lg" href="#proyectos">Ver proyectos</a>
                <a class="cta cta--secondary cta--lg" href="#contacto">Contacto</a>
            </div>

            <?php if (! empty($profile['available_for_work']) || ! empty($profile['location']) || ! empty($profile['cv_path'])): ?>
                <div class="hero__status">
                    <?php if (! empty($profile['available_for_work'])): ?>
                        <p class="status"><span class="status__dot" aria-hidden="true"></span>Disponible para nuevas oportunidades</p>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <p class="meta"><?= e($profile['location']) ?></p>
                    <?php endif; ?>

                    <?php if (! empty($profile['cv_path'])): ?>
                        <a class="cta cta--ghost" href="<?= e($profile['cv_path']) ?>" target="_blank" rel="noopener">Descargar CV<span class="visually-hidden"> (se abre en otra pestaña)</span></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="scroll-cue" aria-hidden="true"></span>
    </div>

    <?php
    // La cinta: las áreas de trabajo en bucle. El separador es un punto o
    // un anillo del sistema generativo, nunca un punto medio de texto.
    $areas = ['Datos', 'Infraestructura', 'Sistemas', 'Redes', 'Seguridad', 'Desarrollo web'];
    $seps  = [
        '<svg viewBox="0 0 18 18" aria-hidden="true"><circle cx="9" cy="9" r="4" fill="currentColor"/></svg>',
        '<svg viewBox="0 0 18 18" aria-hidden="true"><circle cx="9" cy="9" r="6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    ];
    ?>
    <div class="marquee" data-marquee>
        <div class="marquee__track">
            <?php for ($copy = 0; $copy < 2; $copy++): ?>
                <ul class="marquee__group"<?= $copy ? ' aria-hidden="true"' : ' aria-label="Áreas de trabajo"' ?>>
                    <?php foreach ($areas as $i => $area): ?>
                        <li class="marquee__item"><?= e($area) ?></li>
                        <li class="marquee__sep" aria-hidden="true"><?= $seps[$i % 2] ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endfor; ?>
        </div>
    </div>
</section>
