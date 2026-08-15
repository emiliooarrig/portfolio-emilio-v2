<?php
/**
 * Certificaciones.
 *
 * @var array<int, array<string, mixed>> $certifications
 * @var array<int, string> $issuers
 */

use App\Models\Certification;
?>
<section class="section">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Credenciales',
            'title'   => 'Certificaciones',
            'lead'    => 'Formación verificable en ingeniería de datos, nube y analítica. Cada credencial enlaza a su comprobante público.',
            'meta'    => count($certifications) . ' credenciales · ' . count($issuers) . ' emisores',
        ]) ?>

        <?php if ($certifications === []): ?>
            <p class="empty-state">Todavía no hay certificaciones publicadas.</p>
        <?php else: ?>
            <ul class="cert-grid">
                <?php foreach ($certifications as $cert): ?>
                    <?php $status = Certification::status($cert['expires_on']); ?>
                    <li class="cert-card">
                        <div class="cert-card__head">
                            <p class="cert-card__issuer mono"><?= e($cert['issuer']) ?></p>
                            <span class="badge badge--<?= e($status) ?> mono"><?= e($status) ?></span>
                        </div>

                        <h2 class="cert-card__title"><?= e($cert['title']) ?></h2>

                        <?php if (! empty($cert['description'])): ?>
                            <p class="cert-card__desc"><?= e($cert['description']) ?></p>
                        <?php endif; ?>

                        <dl class="cert-card__meta mono">
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
                            <a class="link-arrow" href="<?= e($cert['credential_url']) ?>" target="_blank" rel="noopener">
                                Verificar credencial <span aria-hidden="true">↗</span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
