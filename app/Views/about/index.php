<?php
/**
 * Sobre mí.
 *
 * @var array<string, mixed> $profile
 * @var array<string, array<int, array<string, mixed>>> $stackByCategory
 * @var int|null $careerStart
 * @var array<string, mixed>|null $currentRole
 */

use App\Models\Technology;
?>
<section class="section">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Perfil',
            'title'   => 'Sobre mí',
            'lead'    => $profile['bio_short'] ?: null,
            'meta'    => $careerStart ? 'en tecnología desde ' . $careerStart : null,
        ]) ?>

        <div class="grid-split">
            <div class="prose">
                <?= paragraphs($profile['bio_long']) ?>
            </div>

            <aside class="panel panel--sticky">
                <p class="eyebrow mono">Ficha técnica</p>

                <dl class="spec-list">
                    <?php if ($currentRole): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">Puesto actual</dt>
                            <dd class="spec-list__val"><?= e($currentRole['role']) ?> · <?= e($currentRole['company']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">Ubicación</dt>
                            <dd class="spec-list__val"><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <div class="spec-list__row">
                        <dt class="spec-list__key mono">Experiencia</dt>
                        <dd class="spec-list__val"><?= (int) $profile['years_experience'] ?> años</dd>
                    </div>

                    <?php if (! empty($profile['email'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">Correo</dt>
                            <dd class="spec-list__val">
                                <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <div class="panel__actions">
                    <?php if (! empty($profile['cv_path'])): ?>
                        <a class="cta cta--primary cta--sm" href="<?= e($profile['cv_path']) ?>" target="_blank" rel="noopener">Descargar CV</a>
                    <?php endif; ?>
                    <a class="cta cta--ghost cta--sm" href="<?= url('/contacto') ?>">Contáctame</a>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Herramientas',
            'title'   => 'Con qué trabajo',
            'lead'    => 'Nada exótico por moda: cada pieza está aquí porque resolvió un problema real en producción.',
        ]) ?>

        <div class="stack-grid">
            <?php foreach ($stackByCategory as $category => $items): ?>
                <div class="stack-grid__group">
                    <h2 class="stack-grid__title mono"><?= e(Technology::categoryLabel((string) $category)) ?></h2>
                    <ul class="chip-list chip-list--wrap">
                        <?php foreach ($items as $tech): ?>
                            <li class="chip mono"><?= e($tech['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
