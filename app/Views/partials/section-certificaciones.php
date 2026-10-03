<?php
/**
 * Sección Certificaciones — conjunto, no secuencia: sin numeración.
 * Una fila por credencial; el ID va dentro del aria-label de "Verificar".
 *
 * @var array<int, array<string, mixed>> $certifications
 */
?>
<section class="section" id="certificaciones">
    <div class="container section__grid">
        <?= partial('section-header', [
            'title' => 'Certificaciones',
            'lead'  => 'Cada una enlaza a su comprobante.',
        ]) ?>

        <div class="section__body">
            <?php if ($certifications === []): ?>
                <p class="empty-state">Todavía no hay certificaciones publicadas.</p>
            <?php else: ?>
                <ul class="row-list">
                    <?php foreach ($certifications as $index => $cert): ?>
                        <?php
                        $verifyLabel = 'Verificar ' . $cert['title']
                            . (! empty($cert['credential_id']) ? ', credencial ' . $cert['credential_id'] : '')
                            . ' (se abre en otra pestaña)';
                        ?>
                        <li class="cert-row" data-reveal style="--i: <?= $index ?>">
                            <div>
                                <h3 class="cert-row__title"><?= e($cert['title']) ?></h3>
                                <p class="cert-row__issuer"><?= e($cert['issuer']) ?></p>
                            </div>

                            <p class="meta"><?= e(month_year($cert['issued_on'], '')) ?></p>

                            <p class="meta">
                                <?php if (! empty($cert['expires_on'])): ?>
                                    Vence <?= e(month_year($cert['expires_on'])) ?>
                                <?php endif; ?>
                            </p>

                            <?php if (! empty($cert['credential_url'])): ?>
                                <a class="cert-row__verify" href="<?= e($cert['credential_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($verifyLabel) ?>">Verificar</a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
