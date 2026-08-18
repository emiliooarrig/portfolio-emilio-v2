<?php
/**
 * Certificaciones — el grid de credenciales.
 *
 * Sin etiqueta de vigencia: la sección pública dejó de mostrarla y aquí se
 * ve el dato crudo, la fecha de vencimiento, que es lo que hay en la base.
 *
 * @var array<int, array<string, mixed>> $certifications
 */
?>
<?= partial('admin-head', [
    'title' => 'Certificaciones',
    'lead'  => 'El grid de credenciales. Los títulos son nombres propios: van tal cual los emite cada institución.',
    'meta'   => plural(count($certifications), 'credencial', 'credenciales'),
    'action' => ['label' => 'Nueva certificación', 'href' => '/admin/certificaciones/nueva'],
]) ?>

<?php if ($certifications === []): ?>
    <p class="admin-empty">
        No hay certificaciones cargadas todavía.
        <a href="<?= url('/admin/certificaciones/nueva') ?>">Añade la primera</a>.
    </p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col" class="admin-table__narrow">#</th>
                    <th scope="col">Certificación</th>
                    <th scope="col">Emisor</th>
                    <th scope="col">Emitida</th>
                    <th scope="col">Vence</th>
                    <th scope="col">Credencial</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certifications as $cert): ?>
                    <tr<?= (int) $cert['is_published'] === 1 ? '' : ' class="is-hidden-row"' ?>>
                        <td class="meta admin-table__narrow"><?= (int) $cert['sort_order'] ?></td>

                        <td class="admin-table__wide"><strong><?= e($cert['title']) ?></strong></td>

                        <td><?= e($cert['issuer']) ?></td>

                        <td class="meta"><?= e(month_year($cert['issued_on'], '—')) ?></td>

                        <td class="meta"><?= e(month_year($cert['expires_on'], 'No vence')) ?></td>

                        <td class="admin-table__sub">
                            <?php if (! empty($cert['credential_url'])): ?>
                                <a href="<?= e($cert['credential_url']) ?>" target="_blank" rel="noopener noreferrer">
                                    <?= e($cert['credential_id'] ?: 'Verificar') ?>
                                </a>
                            <?php else: ?>
                                <span class="meta"><?= e($cert['credential_id'] ?: '—') ?></span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'  => (int) $cert['is_published'] === 1,
                                'yes' => 'Publicada',
                                'no'  => 'Oculta',
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-row-actions', [
                                'edit'      => '/admin/certificaciones/' . (int) $cert['id'] . '/editar',
                                'delete'    => '/admin/certificaciones/' . (int) $cert['id'] . '/eliminar',
                                'editLabel' => (string) $cert['title'],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
