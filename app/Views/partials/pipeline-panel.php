<?php
/**
 * Riel del pipeline de datos: celda bento como cualquier otra (sin vidrio).
 *
 * @var array<int, array<string, mixed>> $steps
 * @var string|null $eyebrow
 * @var string|null $title
 * @var string|null $caption
 */

$eyebrow = $eyebrow ?? 'Del dato crudo al insight';
$title   = $title   ?? 'Cómo viaja un dato conmigo';
$caption = $caption ?? 'ingesta → decisión';

if (empty($steps)) {
    return;
}
?>
<div class="pipeline bento-card--hoverable" data-pipeline>
    <div class="pipeline__head">
        <div>
            <p class="eyebrow meta"><?= e($eyebrow) ?></p>
            <h3 class="pipeline__title"><?= e($title) ?></h3>
        </div>
        <p class="pipeline__caption meta"><?= e($caption) ?></p>
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
                <p class="pipeline__label meta"><?= e($step['label']) ?></p>
                <?php if (! empty($step['description'])): ?>
                    <p class="pipeline__desc"><?= e($step['description']) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
