<?php
/**
 * Detalle de proyecto.
 *
 * @var array<string, mixed> $project
 * @var array{prev: array<string, mixed>|null, next: array<string, mixed>|null} $neighbours
 */
?>
<article class="section section--project">
    <div class="container">

        <a class="back-link mono" href="<?= url('/proyectos') ?>">
            <span aria-hidden="true">←</span> Proyectos
        </a>

        <header class="project-head">
            <div class="project-head__main">
                <p class="eyebrow mono">
                    <?= e(month_year($project['started_on'], '')) ?>
                    <?php if (! empty($project['ended_on'])): ?>
                        — <?= e(month_year($project['ended_on'])) ?>
                    <?php endif; ?>
                    <?php if (! empty($project['client'])): ?>
                        · <?= e($project['client']) ?>
                    <?php endif; ?>
                </p>

                <h1 class="project-head__title"><?= e($project['title']) ?></h1>

                <?php if (! empty($project['subtitle'])): ?>
                    <p class="project-head__subtitle"><?= e($project['subtitle']) ?></p>
                <?php endif; ?>

                <ul class="chip-list chip-list--wrap">
                    <?php foreach ($project['technologies'] as $tech): ?>
                        <li class="chip mono"><?= e($tech['name']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (! empty($project['repo_url']) || ! empty($project['demo_url'])): ?>
                    <div class="project-head__links">
                        <?php if (! empty($project['repo_url'])): ?>
                            <a class="cta cta--ghost cta--sm" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener">Ver repositorio</a>
                        <?php endif; ?>
                        <?php if (! empty($project['demo_url'])): ?>
                            <a class="cta cta--ghost cta--sm" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">Ver demo</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (! empty($project['metrics'])): ?>
                <aside class="project-metrics">
                    <p class="eyebrow mono">Resultados</p>
                    <dl class="readout">
                        <?php foreach ($project['metrics'] as $metric): ?>
                            <div class="readout__row">
                                <dt class="readout__label"><?= e($metric['label']) ?></dt>
                                <dd class="readout__value mono">
                                    <?= e($metric['value']) ?><?php if (! empty($metric['unit'])): ?><span class="readout__unit"><?= e($metric['unit']) ?></span><?php endif; ?>
                                </dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </aside>
            <?php endif; ?>
        </header>

        <?php if (! empty($project['has_pipeline']) && ! empty($project['pipeline'])): ?>
            <div class="project-pipeline">
                <?= partial('pipeline-panel', [
                    'steps'   => $project['pipeline'],
                    'eyebrow' => 'Arquitectura',
                    'title'   => 'Flujo de datos del proyecto',
                    'caption' => 'origen → entrega',
                ]) ?>
            </div>
        <?php endif; ?>

        <div class="project-body">
            <?php if (! empty($project['context'])): ?>
                <section class="project-block">
                    <h2 class="project-block__title">Contexto</h2>
                    <div class="prose"><?= paragraphs($project['context']) ?></div>
                </section>
            <?php endif; ?>

            <?php if (! empty($project['solution'])): ?>
                <section class="project-block">
                    <h2 class="project-block__title">Qué construí</h2>
                    <div class="prose"><?= paragraphs($project['solution']) ?></div>
                </section>
            <?php endif; ?>

            <?php if (! empty($project['outcome'])): ?>
                <section class="project-block project-block--outcome">
                    <h2 class="project-block__title">Resultado</h2>
                    <div class="prose"><?= paragraphs($project['outcome']) ?></div>
                </section>
            <?php endif; ?>

            <?php if (! empty($project['role'])): ?>
                <p class="project-role mono">Mi rol: <?= e($project['role']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($neighbours['prev'] || $neighbours['next']): ?>
            <nav class="project-nav" aria-label="Otros proyectos">
                <?php if ($neighbours['prev']): ?>
                    <a class="project-nav__item project-nav__item--prev" href="<?= url('/proyectos/' . $neighbours['prev']['slug']) ?>">
                        <span class="mono">← Anterior</span>
                        <span class="project-nav__title"><?= e($neighbours['prev']['title']) ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($neighbours['next']): ?>
                    <a class="project-nav__item project-nav__item--next" href="<?= url('/proyectos/' . $neighbours['next']['slug']) ?>">
                        <span class="mono">Siguiente →</span>
                        <span class="project-nav__title"><?= e($neighbours['next']['title']) ?></span>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <?= partial('cta-block', [
            'title'     => '¿Un proyecto similar? Hablemos.',
            'text'      => 'Te digo con franqueza si tu caso se parece a este o si necesita otra cosa.',
            'primary'   => ['label' => 'Contáctame', 'href' => '/contacto'],
            'secondary' => ['label' => 'Ver más proyectos', 'href' => '/proyectos'],
            'variant'   => 'inline',
        ]) ?>
    </div>
</article>
