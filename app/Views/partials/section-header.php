<?php
/**
 * Encabezado de sección.
 *
 * El único <h1> del sitio es el nombre en el hero: aquí van <h2>.
 *
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $lead
 * @var string|null $meta   dato corto, alineado a la derecha
 */

$eyebrow = $eyebrow ?? null;
$lead    = $lead    ?? null;
$meta    = $meta    ?? null;
?>
<header class="section-header reveal">
    <div class="section-header__main">
        <?php if ($eyebrow): ?>
            <p class="eyebrow meta"><?= e($eyebrow) ?></p>
        <?php endif; ?>

        <h2 class="section-header__title"><?= e($title) ?></h2>

        <?php if ($lead): ?>
            <p class="section-header__lead"><?= e($lead) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($meta): ?>
        <p class="section-header__meta meta"><?= e($meta) ?></p>
    <?php endif; ?>
</header>
