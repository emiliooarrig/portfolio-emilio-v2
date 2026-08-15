<?php
/**
 * Encabezado de sección con motivo blueprint.
 *
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $lead
 * @var string|null $meta   dato corto en mono, alineado a la derecha
 */

$eyebrow = $eyebrow ?? null;
$lead    = $lead    ?? null;
$meta    = $meta    ?? null;
?>
<header class="section-header">
    <div class="section-header__main">
        <?php if ($eyebrow): ?>
            <p class="eyebrow mono"><?= e($eyebrow) ?></p>
        <?php endif; ?>

        <h1 class="section-header__title"><?= e($title) ?></h1>

        <?php if ($lead): ?>
            <p class="section-header__lead"><?= e($lead) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($meta): ?>
        <p class="section-header__meta mono"><?= e($meta) ?></p>
    <?php endif; ?>
</header>
