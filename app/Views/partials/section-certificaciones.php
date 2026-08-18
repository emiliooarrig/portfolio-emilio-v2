<?php
/**
 * Sección Certificaciones — conjunto, no secuencia: sin numeración.
 *
 * @var array<int, array<string, mixed>> $certifications
 * @var array<int, string> $issuers
 */
?>
<section class="section" id="certificaciones">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Credenciales',
            'title'   => 'Certificaciones',
            'lead'    => 'Exámenes que presenté y aprobé con Google, Microsoft, Amazon y otras. Cada uno enlaza a su comprobante, por si quieres confirmarlo tú mismo.',
            'meta'    => count($certifications) . ' credenciales · ' . count($issuers) . ' emisores',
        ]) ?>

        <?php if ($certifications === []): ?>
            <p class="empty-state">Todavía no hay certificaciones publicadas.</p>
        <?php else: ?>
            <div class="bento bento--certs reveal--stagger">

                <?php foreach ($certifications as $cert): ?>
                    <article class="cert-card bento-card--hoverable">
                        <p class="cert-card__issuer meta"><?= e($cert['issuer']) ?></p>

                        <h3 class="cert-card__title"><?= e($cert['title']) ?></h3>

                        <?php if (! empty($cert['description'])): ?>
                            <p class="cert-card__desc"><?= e($cert['description']) ?></p>
                        <?php endif; ?>

                        <dl class="cert-card__meta meta">
                            <div>
                                <dt>Emitida</dt>
                                <dd><?= e(month_year($cert['issued_on'])) ?></dd>
                            </div>
                            <div>
                                <dt>Vence</dt>
                                <dd><?= e(month_year($cert['expires_on'], 'No expira')) ?></dd>
                            </div>
                            <?php if (! empty($cert['credential_id'])): ?>
                                <div>
                                    <dt>ID</dt>
                                    <dd><?= e($cert['credential_id']) ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>

                        <?php if (! empty($cert['credential_url'])): ?>
                            <div class="cert-card__foot">
                                <a class="link-arrow" href="<?= e($cert['credential_url']) ?>" target="_blank" rel="noopener">
                                    Verificar credencial <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
</section>
