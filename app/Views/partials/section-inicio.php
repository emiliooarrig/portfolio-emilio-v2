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
    </div>
</section>
