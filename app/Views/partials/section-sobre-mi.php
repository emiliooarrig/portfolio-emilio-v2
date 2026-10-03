<?php
/**
 * Sección Sobre mí — bio, ficha sin caja y el stack agrupado.
 *
 * El orden de las categorías del stack lo da el ENUM de `technologies`
 * (Datos y Sistemas primero): `groupedByCategory()` ordena por él.
 *
 * @var array<string, mixed> $profile
 * @var array<string, mixed>|null $currentRole
 * @var array<string, array<int, array<string, mixed>>> $stackByCategory
 */

use App\Models\Technology;
?>
<section class="section" id="sobre-mi">
    <div class="container section__grid">
        <?= partial('section-header', ['title' => 'Sobre mí']) ?>

        <div class="section__body about">
            <div class="about__bio">
                <?php if (! empty($profile['bio_short'])): ?>
                    <p class="about__lead"><?= e($profile['bio_short']) ?></p>
                <?php endif; ?>

                <div class="prose"><?= paragraphs($profile['bio_long']) ?></div>
            </div>

            <div class="about__facts">
                <dl class="facts">
                    <?php if ($currentRole): ?>
                        <div>
                            <dt class="meta">Puesto actual</dt>
                            <dd><?= e($currentRole['role']) ?>, <?= e($currentRole['company']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div>
                            <dt class="meta">Ubicación</dt>
                            <dd><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['email'])): ?>
                        <div>
                            <dt class="meta">Correo</dt>
                            <dd><a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <?php if (! empty($profile['cv_path'])): ?>
                    <a class="cta cta--secondary cta--sm" href="<?= e($profile['cv_path']) ?>" target="_blank" rel="noopener">Descargar CV<span class="visually-hidden"> (se abre en otra pestaña)</span></a>
                <?php endif; ?>
            </div>

            <?php if (! empty($stackByCategory)): ?>
                <div class="stack">
                    <h3 class="stack__title">Con qué trabajo</h3>

                    <div class="stack__groups">
                        <?php foreach ($stackByCategory as $category => $items): ?>
                            <div class="stack__group">
                                <h4 class="meta"><?= e(Technology::categoryLabel((string) $category)) ?></h4>
                                <ul class="tech-list stack__items">
                                    <?php foreach ($items as $tech): ?>
                                        <li><?= e($tech['name']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
