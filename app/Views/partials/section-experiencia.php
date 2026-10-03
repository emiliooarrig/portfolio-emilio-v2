<?php
/**
 * Sección Experiencia — un puesto por fila: fechas a la izquierda, el
 * puesto a la derecha. Aquí el lenguaje técnico se queda tal cual.
 *
 * @var array<int, array<string, mixed>> $experiences
 */

use App\Models\Experience;
?>
<section class="section" id="experiencia">
    <div class="container section__grid">
        <?= partial('section-header', ['title' => 'Experiencia']) ?>

        <div class="section__body">
            <?php if ($experiences === []): ?>
                <p class="empty-state">Todavía no hay experiencia publicada.</p>
            <?php else: ?>
                <ol class="timeline">
                    <?php foreach ($experiences as $index => $exp): ?>
                        <li class="timeline__item" data-reveal style="--i: <?= min($index, 3) ?>">
                            <div class="timeline__when">
                                <?php if (! empty($exp['is_current'])): ?>
                                    <p class="status"><span class="status__dot" aria-hidden="true"></span>Actual</p>
                                <?php endif; ?>
                                <p class="meta"><?= e(date_range($exp['started_on'], $exp['ended_on'])) ?></p>
                                <?php $duration = duration_label($exp['started_on'], $exp['ended_on']); ?>
                                <?php if ($duration !== ''): ?>
                                    <p class="meta"><?= e($duration) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="timeline__content">
                                <h3 class="timeline__role"><?= e($exp['role']) ?></h3>

                                <p class="timeline__company">
                                    <?php if (! empty($exp['company_url'])): ?>
                                        <a href="<?= e($exp['company_url']) ?>" target="_blank" rel="noopener" data-cursor="Abrir"><?= e($exp['company']) ?><span class="visually-hidden"> (se abre en otra pestaña)</span></a><?php else: ?><?= e($exp['company']) ?><?php endif; ?><?php if (! empty($exp['location'])): ?>, <?= e($exp['location']) ?><?php endif; ?>
                                </p>

                                <p class="timeline__type meta"><?= e(Experience::employmentLabel((string) $exp['employment_type'])) ?></p>

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
        </div>
    </div>
</section>
