<?php
/**
 * Experiencia laboral — timeline (única sección con marcadores numerados).
 *
 * @var array<int, array<string, mixed>> $experiences
 * @var int|null $careerStart
 * @var array<string, mixed> $profile
 */

use App\Models\Experience;
?>
<section class="section">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Trayectoria',
            'title'   => 'Experiencia laboral',
            'lead'    => 'De administrar servidores a diseñar plataformas de datos. La secuencia importa: cada puesto explica el siguiente.',
            'meta'    => $careerStart ? $careerStart . ' → hoy' : null,
        ]) ?>

        <?php if ($experiences === []): ?>
            <p class="empty-state">Todavía no hay experiencia publicada.</p>
        <?php else: ?>
            <ol class="timeline">
                <?php foreach ($experiences as $index => $exp): ?>
                    <li class="timeline__item<?= ! empty($exp['is_current']) ? ' timeline__item--current' : '' ?>">
                        <div class="timeline__marker" aria-hidden="true">
                            <span class="timeline__index mono"><?= str_pad((string) (count($experiences) - $index), 2, '0', STR_PAD_LEFT) ?></span>
                        </div>

                        <div class="timeline__content">
                            <div class="timeline__head">
                                <div>
                                    <h2 class="timeline__role"><?= e($exp['role']) ?></h2>
                                    <p class="timeline__company">
                                        <?php if (! empty($exp['company_url'])): ?>
                                            <a href="<?= e($exp['company_url']) ?>" target="_blank" rel="noopener"><?= e($exp['company']) ?></a>
                                        <?php else: ?>
                                            <?= e($exp['company']) ?>
                                        <?php endif; ?>
                                        <?php if (! empty($exp['location'])): ?>
                                            <span class="timeline__location">· <?= e($exp['location']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <p class="timeline__dates mono">
                                    <?= e(date_range($exp['started_on'], $exp['ended_on'])) ?>
                                    <span class="timeline__duration"><?= e(duration_label($exp['started_on'], $exp['ended_on'])) ?></span>
                                </p>
                            </div>

                            <p class="timeline__type mono"><?= e(Experience::employmentLabel((string) $exp['employment_type'])) ?></p>

                            <?php if (! empty($exp['summary'])): ?>
                                <p class="timeline__summary"><?= e($exp['summary']) ?></p>
                            <?php endif; ?>

                            <?php if (! empty($exp['highlights'])): ?>
                                <ul class="timeline__highlights">
                                    <?php foreach ($exp['highlights'] as $highlight): ?>
                                        <li><?= e($highlight['description']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <?php if (! empty($profile['cv_path'])): ?>
            <div class="section__foot">
                <a class="cta cta--ghost" href="<?= e($profile['cv_path']) ?>" target="_blank" rel="noopener">Ver CV completo</a>
            </div>
        <?php endif; ?>
    </div>
</section>
