<?php
/**
 * Inicio — hero como bento.
 *
 * @var array<string, mixed> $profile
 * @var array<int, array<string, mixed>> $stack
 * @var array<int, array<string, mixed>> $metrics
 * @var array<int, array<string, mixed>> $pipeline
 * @var array<int, array<string, mixed>> $featuredProjects
 * @var array<string, mixed>|null $currentRole
 * @var int $certCount
 * @var int $projectCount
 */
?>
<section class="section section--hero">
    <div class="container">
        <div class="bento bento--hero">

            <div class="bento-cell bento-cell--hero">
                <?php if (! empty($profile['available_for_work'])): ?>
                    <p class="status-pill mono">
                        <span class="status-pill__dot" aria-hidden="true"></span>
                        Disponible para proyectos de datos
                    </p>
                <?php endif; ?>

                <h1 class="hero__name"><?= e($profile['full_name']) ?></h1>
                <p class="hero__role mono"><?= e($profile['role_title']) ?></p>
                <p class="hero__headline"><?= e($profile['headline']) ?></p>

                <div class="hero__actions">
                    <a class="cta cta--primary" href="<?= url('/proyectos') ?>">Ver proyectos</a>
                    <a class="cta cta--secondary" href="<?= url('/contacto') ?>">Contáctame</a>
                </div>
            </div>

            <div class="bento-cell bento-cell--status">
                <p class="eyebrow mono">Ahora mismo</p>

                <?php if ($currentRole): ?>
                    <p class="status-card__role"><?= e($currentRole['role']) ?></p>
                    <p class="status-card__company mono">
                        <?= e($currentRole['company']) ?> · desde <?= e(month_year($currentRole['started_on'])) ?>
                    </p>
                <?php else: ?>
                    <p class="status-card__role">Ingeniería de datos independiente</p>
                <?php endif; ?>

                <?php if (! empty($profile['location'])): ?>
                    <p class="status-card__location mono"><?= e($profile['location']) ?></p>
                <?php endif; ?>
            </div>

            <div class="bento-cell bento-cell--readout">
                <p class="eyebrow mono">Lectura rápida</p>

                <dl class="readout">
                    <?php foreach ($metrics as $metric): ?>
                        <div class="readout__row">
                            <dt class="readout__label"><?= e($metric['label']) ?></dt>
                            <dd class="readout__value mono">
                                <?= e($metric['value']) ?><?php if (! empty($metric['unit'])): ?><span class="readout__unit"><?= e($metric['unit']) ?></span><?php endif; ?>
                            </dd>
                        </div>
                    <?php endforeach; ?>

                    <div class="readout__row">
                        <dt class="readout__label">Proyectos publicados</dt>
                        <dd class="readout__value mono"><?= $projectCount ?></dd>
                    </div>
                    <div class="readout__row">
                        <dt class="readout__label">Certificaciones</dt>
                        <dd class="readout__value mono"><?= $certCount ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bento-cell bento-cell--pipeline">
                <?= partial('pipeline-panel', [
                    'steps'   => $pipeline,
                    'eyebrow' => 'Del dato crudo al insight',
                    'title'   => 'Cómo viaja un dato conmigo',
                    'caption' => 'ingesta → decisión',
                ]) ?>
            </div>

            <div class="bento-cell bento-cell--stack">
                <p class="eyebrow mono">Stack principal</p>

                <ul class="chip-list chip-list--wrap">
                    <?php foreach ($stack as $tech): ?>
                        <li class="chip mono"><?= e($tech['name']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <a class="link-arrow" href="<?= url('/sobre-mi') ?>">
                    Stack completo
                    <span aria-hidden="true">→</span>
                </a>
            </div>

        </div>
    </div>
</section>

<section class="section section--projects">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Trabajo seleccionado',
            'title'   => 'Proyectos destacados',
            'lead'    => 'Tres piezas que resumen cómo trabajo: ingesta confiable, modelado con criterio y un resultado que alguien puede usar.',
            'meta'    => $projectCount . ' publicados',
        ]) ?>

        <div class="bento bento--projects">
            <?php
            // 7 + 5 en la primera fila, franja completa abajo: la retícula cierra sin huecos.
            $previewSizes = ['lg', 'md', 'xl'];
            ?>
            <?php foreach ($featuredProjects as $index => $project): ?>
                <?= partial('bento-card', [
                    'project' => $project,
                    'size'    => $previewSizes[$index] ?? 'md',
                ]) ?>
            <?php endforeach; ?>
        </div>

        <?php // El CTA de contacto vive en el footer global; aquí basta el puente a la sección completa. ?>
        <div class="section__foot">
            <a class="link-arrow" href="<?= url('/proyectos') ?>">
                Ver los <?= $projectCount ?> proyectos
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</section>
