<?php
/**
 * Sección Inicio — hero a pantalla completa.
 *
 * Deliberadamente fuera del bento: es lo único del sitio que no vive en una
 * celda. Sólo nombre, profesión y las dos llamadas a la acción; todo lo demás
 * (puesto actual, métricas, stack) vive en su sección correspondiente.
 *
 * @var array<string, mixed> $profile
 */

// El nombre entra palabra por palabra: cada una lleva su índice de retardo.
$words = preg_split('/\s+/u', trim((string) $profile['full_name'])) ?: [];
?>
<section class="hero" id="inicio">

    <div class="hero__canvas" aria-hidden="true">
        <span class="hero__grid"></span>
        <span class="hero__aurora hero__aurora--blue"></span>
        <span class="hero__aurora hero__aurora--amber"></span>
    </div>

    <div class="hero__inner">
        <h1 class="hero__name">
            <?php foreach ($words as $index => $word): ?>
                <span class="hero__word" style="--word-index: <?= $index ?>"><?= e($word) ?></span>
            <?php endforeach; ?>
        </h1>

        <p class="hero__role meta" style="--word-index: <?= count($words) ?>">
            <?= e($profile['role_title']) ?>
        </p>

        <div class="hero__actions" style="--word-index: <?= count($words) + 1 ?>">
            <a class="cta cta--primary cta--lg" href="#servicios">
                Ver en qué te ayudo
                <span class="cta__icon" aria-hidden="true">→</span>
            </a>
            <a class="cta cta--secondary cta--lg" href="#contacto">Contáctame</a>
        </div>
    </div>

    <?php // Riel del ancho completo: cierra el hero y anuncia la narrativa de color. ?>
    <span class="hero__rail" aria-hidden="true"></span>
</section>
