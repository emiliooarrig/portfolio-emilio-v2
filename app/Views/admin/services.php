<?php
/**
 * Servicios — fuera de la landing; se conservan la tabla y el CRUD.
 *
 * @var array<int, array<string, mixed>> $services
 */
?>
<?= partial('admin-head', [
    'title' => 'Servicios',
    'lead'  => 'Hoy no se muestran en la landing: la sección salió del portafolio. La tabla y el CRUD se conservan por si vuelven.',
    'meta'   => plural(count($services), 'servicio', 'servicios'),
    'action' => ['label' => 'Nuevo servicio', 'href' => '/admin/servicios/nuevo'],
]) ?>

<?php if ($services === []): ?>
    <p class="admin-empty">
        No hay servicios cargados todavía.
        <a href="<?= url('/admin/servicios/nuevo') ?>">Crea el primero</a>.
    </p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col" class="admin-table__narrow">ID</th>
                    <th scope="col">Servicio</th>
                    <th scope="col">Gancho</th>
                    <th scope="col">Entregables</th>
                    <th scope="col">Plazo</th>
                    <th scope="col">Destacado</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr<?= (int) $service['is_published'] === 1 ? '' : ' class="is-hidden-row"' ?>>
                        <td class="meta admin-table__narrow"><?= (int) $service['id'] ?></td>

                        <td>
                            <strong><?= e($service['title']) ?></strong>
                            <span class="admin-table__sub meta"><?= e($service['slug']) ?></span>
                        </td>

                        <td class="admin-table__wide"><?= e($service['tagline'] ?: excerpt($service['description'], 90)) ?></td>

                        <td class="admin-table__sub">
                            <?= e(plural(count($service['deliverables']), 'viñeta', 'viñetas')) ?>
                        </td>

                        <td class="meta"><?= e($service['timeframe'] ?: '—') ?></td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'    => (int) $service['is_featured'] === 1,
                                'yes'   => 'Destacado',
                                'no'    => '—',
                                'quiet' => true,
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'  => (int) $service['is_published'] === 1,
                                'yes' => 'Publicado',
                                'no'  => 'Oculto',
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-row-actions', [
                                'edit'        => '/admin/servicios/' . (int) $service['id'] . '/editar',
                                'delete'      => '/admin/servicios/' . (int) $service['id'] . '/eliminar',
                                'name'        => (string) $service['title'],
                                'confirmText' => 'Desaparece del carrusel de servicios, con sus entregables. No hay deshacer.',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
