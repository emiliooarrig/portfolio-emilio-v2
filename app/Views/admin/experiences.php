<?php
/**
 * Experiencia — el timeline laboral.
 *
 * @var array<int, array<string, mixed>> $experiences
 */

use App\Models\Experience;
?>
<?= partial('admin-head', [
    'title' => 'Experiencia',
    'lead'  => 'El timeline laboral. Es la sección que sí habla en técnico: aquí no se simplifica el lenguaje.',
    'meta'   => plural(count($experiences), 'puesto', 'puestos'),
    'action' => ['label' => 'Nuevo puesto', 'href' => '/admin/experiencia/nuevo'],
]) ?>

<?php if ($experiences === []): ?>
    <p class="admin-empty">
        No hay experiencia cargada todavía.
        <a href="<?= url('/admin/experiencia/nuevo') ?>">Añade el primer puesto</a>.
    </p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col" class="admin-table__narrow">ID</th>
                    <th scope="col">Puesto</th>
                    <th scope="col">Empresa</th>
                    <th scope="col">Contratación</th>
                    <th scope="col">Periodo</th>
                    <th scope="col">Duración</th>
                    <th scope="col">Logros</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($experiences as $experience): ?>
                    <tr<?= (int) $experience['is_published'] === 1 ? '' : ' class="is-hidden-row"' ?>>
                        <td class="meta admin-table__narrow"><?= (int) $experience['id'] ?></td>

                        <td>
                            <strong><?= e($experience['role']) ?></strong>
                            <?php if ((int) $experience['is_current'] === 1): ?>
                                <span class="admin-table__sub">puesto actual</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= e($experience['company']) ?>
                            <?php if (! empty($experience['location'])): ?>
                                <span class="admin-table__sub"><?= e($experience['location']) ?></span>
                            <?php endif; ?>
                        </td>

                        <td class="admin-table__sub"><?= e(Experience::employmentLabel((string) $experience['employment_type'])) ?></td>

                        <td class="meta"><?= e(date_range($experience['started_on'], $experience['ended_on'])) ?></td>

                        <td class="meta"><?= e(duration_label($experience['started_on'], $experience['ended_on'])) ?></td>

                        <td class="meta"><?= (int) $experience['highlight_count'] ?></td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'  => (int) $experience['is_published'] === 1,
                                'yes' => 'Publicada',
                                'no'  => 'Oculta',
                            ]) ?>
                        </td>

                        <td>
                            <?= partial('admin-row-actions', [
                                'edit'        => '/admin/experiencia/' . (int) $experience['id'] . '/editar',
                                'delete'      => '/admin/experiencia/' . (int) $experience['id'] . '/eliminar',
                                'name'        => (string) $experience['role'],
                                'confirmText' => 'Se borra el puesto y con él sus logros. Desaparece del timeline. No hay deshacer.',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
