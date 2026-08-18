<?php
/**
 * Contenido del detalle de proyecto. Se sirve como fragmento al modal
 * (fetch) o se inyecta en el shell cuando se entra directo a la URL.
 *
 * @var array<string, mixed> $project
 */
?>
<article class="project-detail">

    <header class="project-detail__head">
        <div class="project-detail__main">
            <p class="eyebrow meta">
                <?= e(month_year($project['started_on'], '')) ?>
                <?php if (! empty($project['ended_on'])): ?>
                    — <?= e(month_year($project['ended_on'])) ?>
                <?php endif; ?>
                <?php if (! empty($project['client'])): ?>
                    · <?= e($project['client']) ?>
                <?php endif; ?>
            </p>

            <h2 class="project-detail__title" id="project-modal-title"><?= e($project['title']) ?></h2>

            <?php if (! empty($project['subtitle'])): ?>
                <p class="project-detail__subtitle"><?= e($project['subtitle']) ?></p>
            <?php endif; ?>

            <ul class="chip-list chip-list--wrap chip-list--animated">
                <?php foreach ($project['technologies'] as $tech): ?>
                    <li class="chip meta"><?= e($tech['name']) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (! empty($project['repo_url']) || ! empty($project['demo_url'])): ?>
                <div class="project-detail__links">
                    <?php if (! empty($project['repo_url'])): ?>
                        <a class="cta cta--ghost cta--sm" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener">
                            Ver repositorio
                            <span class="cta__icon" aria-hidden="true">↗</span>
                        </a>
                    <?php endif; ?>
                    <?php if (! empty($project['demo_url'])): ?>
                        <a class="cta cta--ghost cta--sm" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener">
                            Ver demo
                            <span class="cta__icon" aria-hidden="true">↗</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (! empty($project['metrics'])): ?>
            <aside class="project-detail__metrics bento-card--hoverable">
                <p class="eyebrow meta">Resultados</p>
                <dl class="readout">
                    <?php foreach ($project['metrics'] as $metric): ?>
                        <div class="readout__row">
                            <dt class="readout__label"><?= e($metric['label']) ?></dt>
                            <dd class="readout__value meta">
                                <?= e($metric['value']) ?><?php if (! empty($metric['unit'])): ?><span class="readout__unit"><?= e($metric['unit']) ?></span><?php endif; ?>
                            </dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </aside>
        <?php endif; ?>
    </header>

    <?php if (! empty($project['has_pipeline']) && ! empty($project['pipeline'])): ?>
        <div class="project-detail__pipeline">
            <?= partial('pipeline-panel', [
                'steps'   => $project['pipeline'],
                'eyebrow' => 'Arquitectura',
                'title'   => 'Flujo de datos del proyecto',
                'caption' => 'origen → entrega',
            ]) ?>
        </div>
    <?php endif; ?>

    <div class="project-detail__body">
        <?php if (! empty($project['context'])): ?>
            <section class="project-block">
                <h3 class="project-block__title">Contexto</h3>
                <div class="prose"><?= paragraphs($project['context']) ?></div>
            </section>
        <?php endif; ?>

        <?php if (! empty($project['solution'])): ?>
            <section class="project-block">
                <h3 class="project-block__title">Qué construí</h3>
                <div class="prose"><?= paragraphs($project['solution']) ?></div>
            </section>
        <?php endif; ?>

        <?php if (! empty($project['outcome'])): ?>
            <section class="project-block project-block--outcome bento-card--hoverable">
                <h3 class="project-block__title">Resultado</h3>
                <div class="prose"><?= paragraphs($project['outcome']) ?></div>
            </section>
        <?php endif; ?>

        <?php if (! empty($project['role'])): ?>
            <p class="project-detail__role meta">Mi rol: <?= e($project['role']) ?></p>
        <?php endif; ?>
    </div>

    <?php // CTA contextual: ambos cierran el modal y llevan el scroll a su ancla. ?>
    <div class="project-detail__foot">
        <div class="cta-block cta-block--modal">
            <div class="cta-block__copy">
                <h3 class="cta-block__title">¿Un proyecto similar? Hablemos.</h3>
                <p class="cta-block__text">Te digo con franqueza si tu caso se parece a este o si necesita otra cosa.</p>
            </div>

            <div class="cta-block__actions">
                <a class="cta cta--primary" href="<?= url('/') ?>#contacto" data-modal-scroll="#contacto">
                    Contáctame
                    <span class="cta__icon" aria-hidden="true">→</span>
                </a>
                <a class="cta cta--secondary" href="<?= url('/') ?>#proyectos" data-modal-scroll="#proyectos">
                    Ver más proyectos
                </a>
            </div>
        </div>
    </div>
</article>
