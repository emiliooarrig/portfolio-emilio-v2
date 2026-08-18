<?php
/**
 * Sección Sobre mí — bio, ficha técnica y stack.
 *
 * @var array<string, mixed> $profile
 * @var array<string, mixed>|null $currentRole
 * @var int|null $careerStart
 * @var array<string, array<int, array<string, mixed>>> $stackByCategory
 */

use App\Models\Technology;
?>
<section class="section" id="sobre-mi">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Quién soy',
            'title'   => 'Sobre mí',
            'lead'    => 'Me toca la parte que nadie ve, para que lo que sí se ve funcione y no te dé problemas.',
            'meta'    => $careerStart ? 'en tecnología desde ' . $careerStart : null,
        ]) ?>

        <div class="bento bento--about reveal--stagger">

            <div class="bento-cell bento-cell--bio bento-card--hoverable">
                <?php if (! empty($profile['bio_short'])): ?>
                    <p class="about__lead"><?= e($profile['bio_short']) ?></p>
                <?php endif; ?>

                <div class="prose"><?= paragraphs($profile['bio_long']) ?></div>
            </div>

            <aside class="bento-cell bento-cell--facts bento-card--hoverable">
                <p class="eyebrow meta">En corto</p>

                <dl class="spec-list">
                    <?php if ($currentRole): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">Puesto actual</dt>
                            <dd class="spec-list__val"><?= e($currentRole['role']) ?> · <?= e($currentRole['company']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">Ubicación</dt>
                            <dd class="spec-list__val"><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <div class="spec-list__row">
                        <dt class="spec-list__key meta">Experiencia</dt>
                        <dd class="spec-list__val"><?= (int) $profile['years_experience'] ?> años</dd>
                    </div>

                    <?php if (! empty($profile['email'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">Correo</dt>
                            <dd class="spec-list__val">
                                <a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <div class="bento-cell__actions">
                    <?php if (! empty($profile['cv_path'])): ?>
                        <a class="cta cta--primary cta--sm" href="<?= e($profile['cv_path']) ?>" target="_blank" rel="noopener">Descargar CV</a>
                    <?php endif; ?>
                    <a class="cta cta--ghost cta--sm" href="#contacto">Contáctame</a>
                </div>
            </aside>

            <?php if (! empty($stackByCategory)): ?>
                <div class="bento-cell bento-cell--label">
                    <p class="eyebrow meta">Con qué trabajo</p>
                </div>

                <?php foreach ($stackByCategory as $category => $items): ?>
                    <div class="stack-group bento-card--hoverable">
                        <h3 class="stack-group__title meta"><?= e(Technology::categoryLabel((string) $category)) ?></h3>
                        <ul class="chip-list chip-list--wrap chip-list--animated">
                            <?php foreach ($items as $tech): ?>
                                <li class="chip meta"><?= e($tech['name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</section>
