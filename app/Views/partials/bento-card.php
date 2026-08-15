<?php
/**
 * Tarjeta de proyecto dentro del grid bento.
 *
 * @var array<string, mixed> $project
 * @var string|null $size   sm | md | lg | xl — sobrescribe project.bento_size
 */

$size  = $size ?? ($project['bento_size'] ?? 'md');
$techs = array_slice($project['technologies'] ?? [], 0, 3);
$extra = max(0, count($project['technologies'] ?? []) - count($techs));
?>
<article class="bento-card bento-card--<?= e($size) ?><?= ! empty($project['is_featured']) ? ' bento-card--featured' : '' ?>">
    <a class="bento-card__link" href="<?= url('/proyectos/' . $project['slug']) ?>">
        <span class="visually-hidden">Ver proyecto <?= e($project['title']) ?></span>
    </a>

    <div class="bento-card__head">
        <p class="bento-card__period mono">
            <?= e(month_year($project['started_on'] ?? null, '')) ?>
            <?php if (! empty($project['ended_on'])): ?>
                — <?= e(month_year($project['ended_on'])) ?>
            <?php elseif (! empty($project['started_on'])): ?>
                — en curso
            <?php endif; ?>
        </p>
        <?php if (! empty($project['is_featured'])): ?>
            <span class="badge badge--accent mono">Destacado</span>
        <?php endif; ?>
    </div>

    <div class="bento-card__body">
        <h3 class="bento-card__title"><?= e($project['title']) ?></h3>

        <?php if (! empty($project['subtitle'])): ?>
            <p class="bento-card__subtitle"><?= e($project['subtitle']) ?></p>
        <?php endif; ?>

        <p class="bento-card__summary"><?= e(excerpt($project['summary'], $size === 'sm' ? 90 : 190)) ?></p>
    </div>

    <div class="bento-card__foot">
        <ul class="chip-list">
            <?php foreach ($techs as $tech): ?>
                <li class="chip mono"><?= e($tech['name']) ?></li>
            <?php endforeach; ?>
            <?php if ($extra > 0): ?>
                <li class="chip chip--ghost mono">+<?= $extra ?></li>
            <?php endif; ?>
        </ul>

        <span class="bento-card__arrow" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75">
                <path d="M5 12h14M13 6l6 6-6 6"></path>
            </svg>
        </span>
    </div>
</article>
