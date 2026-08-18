<?php
/**
 * Stack — los chips de «Sobre mí» y los tags de cada proyecto.
 *
 * @var array<int, array<string, mixed>> $technologies
 */

use App\Models\Technology;

$unused = count(array_filter(
    $technologies,
    static fn (array $tech): bool => (int) $tech['project_count'] === 0
));
?>
<?= partial('admin-head', [
    'title' => 'Stack',
    'lead'  => 'Los chips agrupados por categoría en «Sobre mí» y los tags de cada proyecto.',
    'meta'   => plural(count($technologies), 'tecnología', 'tecnologías')
        . ($unused > 0 ? ' · ' . $unused . ' sin proyecto' : ''),
    'action' => ['label' => 'Nueva tecnología', 'href' => '/admin/tecnologias/nueva'],
]) ?>

<?php if ($technologies === []): ?>
    <p class="admin-empty">
        No hay tecnologías cargadas todavía.
        <a href="<?= url('/admin/tecnologias/nueva') ?>">Añade la primera</a>.
    </p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Categoría</th>
                    <th scope="col">Tecnología</th>
                    <th scope="col">Slug</th>
                    <th scope="col" class="admin-table__narrow">#</th>
                    <th scope="col">Proyectos</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($technologies as $tech): ?>
                    <tr>
                        <td class="admin-table__sub"><?= e(Technology::categoryLabel((string) $tech['category'])) ?></td>

                        <td><strong><?= e($tech['name']) ?></strong></td>

                        <td class="meta admin-table__none"><?= e($tech['slug']) ?></td>

                        <td class="meta admin-table__narrow"><?= (int) $tech['sort_order'] ?></td>

                        <?php // Cero no es un error, pero sí un dato: ese chip no lo respalda ningún proyecto. ?>
                        <td class="meta<?= (int) $tech['project_count'] === 0 ? ' admin-table__none' : '' ?>">
                            <?= (int) $tech['project_count'] ?>
                        </td>

                        <td>
                            <?= partial('admin-row-actions', [
                                'edit'      => '/admin/tecnologias/' . (int) $tech['id'] . '/editar',
                                'delete'    => '/admin/tecnologias/' . (int) $tech['id'] . '/eliminar',
                                'editLabel' => (string) $tech['name'],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
