<?php
/**
 * Una fila del índice de proyectos.
 *
 * El enlace real es el título y apunta a /proyectos/{slug}, así funciona
 * sin JS y es compartible; su ::after se estira sobre la fila completa.
 * Con JS, `project-modal.js` intercepta el clic y abre el detalle encima.
 *
 * @var array<string, mixed> $project
 * @var bool                 $featured  fila ampliada, con métrica principal
 * @var int                  $index     posición en el índice (escalonado de la aparición)
 */

$featured = $featured ?? false;
$index    = (int) ($index ?? 0);
$period   = date_range($project['started_on'] ?? null, $project['ended_on'] ?? null);
$metric   = $project['lead_metric'] ?? null;
?>
<?php if ($featured): ?>
    <article class="project-row project-row--featured" data-project-card data-reveal style="--i: <?= $index ?>">
        <?php if ($period !== ''): ?>
            <p class="project-row__period meta"><?= e($period) ?></p>
        <?php endif; ?>

        <h3 class="project-row__title">
            <a class="project-row__link" href="<?= url('/proyectos/' . $project['slug']) ?>" data-project-link><?= e($project['title']) ?></a>
        </h3>

        <?php if (! empty($project['subtitle'])): ?>
            <p class="project-row__subtitle"><?= e($project['subtitle']) ?></p>
        <?php endif; ?>

        <?php if (! empty($project['technologies'])): ?>
            <ul class="tech-list project-row__tech meta" aria-label="Tecnologías">
                <?php foreach (array_slice($project['technologies'], 0, 4) as $tech): ?>
                    <li><?= e($tech['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($metric): ?>
            <p class="project-row__metric">
                <span class="project-row__metric-value"><?= e($metric['value']) ?><?php if (! empty($metric['unit'])): ?><span class="project-row__metric-unit"><?= e($metric['unit']) ?></span><?php endif; ?></span>
                <span class="project-row__metric-label meta"><?= e($metric['label']) ?></span>
            </p>
        <?php endif; ?>
    </article>
<?php else: ?>
    <article class="project-row" data-project-card data-reveal style="--i: <?= $index ?>">
        <h3 class="project-row__title">
            <a class="project-row__link" href="<?= url('/proyectos/' . $project['slug']) ?>" data-project-link><?= e($project['title']) ?></a>
        </h3>

        <?php if (! empty($project['subtitle'])): ?>
            <p class="project-row__subtitle"><?= e($project['subtitle']) ?></p>
        <?php endif; ?>

        <p class="project-row__year meta">
            <?php if (! empty($project['ended_on'])): ?>
                <?= e(date('Y', strtotime((string) $project['ended_on']))) ?>
            <?php elseif (! empty($project['started_on'])): ?>
                en curso
            <?php endif; ?>
        </p>
    </article>
<?php endif; ?>
