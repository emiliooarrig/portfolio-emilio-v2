<?php
/**
 * Signature element — panel de vidrio con el pipeline de datos.
 * Único lugar del sitio con tratamiento Liquid Glass.
 *
 * @var array<int, array<string, mixed>> $steps
 * @var string|null $eyebrow
 * @var string|null $title
 * @var string|null $caption
 */

$eyebrow = $eyebrow ?? 'Signature';
$title   = $title   ?? 'Cómo viaja un dato conmigo';
$caption = $caption ?? 'Crudo a la izquierda, decisión a la derecha.';

if (empty($steps)) {
    return;
}
?>
<div class="glass-panel pipeline" data-pipeline>
    <div class="pipeline__head">
        <div>
            <p class="eyebrow mono"><?= e($eyebrow) ?></p>
            <h2 class="pipeline__title"><?= e($title) ?></h2>
        </div>
        <p class="pipeline__caption mono"><?= e($caption) ?></p>
    </div>

    <div class="pipeline__flow" aria-hidden="true">
        <span class="pipeline__rail"></span>
        <?php for ($i = 0; $i < 6; $i++): ?>
            <span class="pipeline__packet" style="--packet-index: <?= $i ?>"></span>
        <?php endfor; ?>
    </div>

    <ol class="pipeline__steps">
        <?php foreach ($steps as $index => $step): ?>
            <li class="pipeline__step pipeline__step--<?= e($step['stage']) ?>" style="--step-index: <?= $index ?>">
                <span class="pipeline__node" aria-hidden="true"></span>
                <p class="pipeline__label mono"><?= e($step['label']) ?></p>
                <?php if (! empty($step['description'])): ?>
                    <p class="pipeline__desc"><?= e($step['description']) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
