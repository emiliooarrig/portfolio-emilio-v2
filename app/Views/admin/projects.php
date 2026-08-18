<?php
/**
 * Proyectos — lo que alimenta el grid bento y el modal de detalle.
 *
 * @var array<int, array<string, mixed>> $projects
 */
?>
<?= partial('admin-head', [
    'title' => 'Proyectos',
    'lead'  => 'Alimentan el grid de «Proyectos destacados» y el modal de detalle.',
    'meta'   => plural(count($projects), 'proyecto', 'proyectos'),
    'action' => ['label' => 'Nuevo proyecto', 'href' => '/admin/proyectos/nuevo'],
]) ?>

<?php if ($projects === []): ?>
    <p class="admin-empty">
        No hay proyectos cargados todavía.
        <a href="<?= url('/admin/proyectos/nuevo') ?>">Crea el primero</a>.
    </p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col" class="admin-table__narrow">#</th>
                    <th scope="col">Proyecto</th>
                    <th scope="col">Stack</th>
                    <th scope="col">Bento</th>
                    <th scope="col">Periodo</th>
                    <th scope="col">Detalle</th>
                    <th scope="col">Destacado</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr<?= (int) $project['is_published'] === 1 ? '' : ' class="is-hidden-row"' ?>>
                        <td class="meta admin-table__narrow"><?= (int) $project['sort_order'] ?></td>

                        <td>
                            <strong><?= e($project['title']) ?></strong>
                            <span class="admin-table__sub meta">/proyectos/<?= e($project['slug']) ?></span>
                        </td>

                        <td>
                            <?php $stack = $project['technologies']; ?>
                            <?php if ($stack === []): ?>
                                <span class="admin-table__none">sin stack</span>
                            <?php else: ?>
                                <span class="admin-chips">
                                    <?php foreach (array_slice($stack, 0, 3) as $tech): ?>
                                        <span class="chip"><?= e($tech['name']) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($stack) > 3): ?>
                                        <span class="chip chip--ghost">+<?= count($stack) - 3 ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="meta"><?= e(strtoupper((string) $project['bento_size'])) ?></td>

                        <td class="meta"><?= e(date_range($project['started_on'], $project['ended_on'])) ?></td>

                        <td class="admin-table__sub">
                            <?= e(plural((int) $project['metric_count'], 'métrica', 'métricas')) ?>
                            <?php if ((int) $project['has_pipeline'] === 1): ?>
                                · <?= e(plural((int) $project['step_count'], 'paso de flujo', 'pasos de flujo')) ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'    => (int) $project['is_featured'] === 1,
                                'yes'   => 'Destacado',
                                'no'    => '—',
                                'quiet' => true,
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'  => (int) $project['is_published'] === 1,
                                'yes' => 'Publicado',
                                'no'  => 'Oculto',
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-row-actions', [
                                'edit'      => '/admin/proyectos/' . (int) $project['id'] . '/editar',
                                'delete'    => '/admin/proyectos/' . (int) $project['id'] . '/eliminar',
                                'editLabel' => (string) $project['title'],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
