<?php
/**
 * Mensajes — la bandeja del formulario de contacto.
 *
 * Es la única tabla del panel que va al revés que las demás: no alimenta la
 * landing, la recibe.
 *
 * @var array<int, array<string, mixed>> $messages
 */

$unread = count(array_filter(
    $messages,
    static fn (array $message): bool => (int) $message['is_read'] === 0
));
?>
<?= partial('admin-head', [
    'title' => 'Mensajes',
    'lead'  => 'Lo que llega por el formulario de contacto, lo más reciente primero.',
    'meta'  => count($messages) . ' en total'
        . ($unread > 0 ? ' · ' . $unread . ' sin leer' : ''),
]) ?>

<?php if ($messages === []): ?>
    <p class="admin-empty">Nadie ha escrito todavía por el formulario de contacto.</p>
<?php else: ?>
    <div class="admin-table__wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Recibido</th>
                    <th scope="col">De</th>
                    <th scope="col">Asunto</th>
                    <th scope="col">Mensaje</th>
                    <th scope="col">Origen</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr<?= (int) $message['is_read'] === 0 ? ' class="is-unread-row"' : '' ?>>
                        <td class="meta"><?= e(date('d/m/Y H:i', (int) strtotime((string) $message['created_at']))) ?></td>

                        <td>
                            <strong><?= e($message['name']) ?></strong>
                            <a class="admin-table__sub" href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a>
                        </td>

                        <td>
                            <a href="<?= url('/admin/mensajes/' . (int) $message['id']) ?>">
                                <?= e($message['subject'] ?: 'Sin asunto') ?>
                            </a>
                        </td>

                        <?php // Recortado a propósito: la tabla es para barrer, no para leer entero. ?>
                        <td class="admin-table__wide"><?= e(excerpt($message['message'], 140)) ?></td>

                        <td class="meta admin-table__none"><?= e($message['ip_address'] ?: '—') ?></td>

                        <td>
                            <?= partial('admin-flag', [
                                'on'  => (int) $message['is_read'] === 1,
                                'yes' => 'Leído',
                                'no'  => 'Nuevo',
                            ]) ?>
                        </td>

                        <td>
                            <span class="admin-row-actions">
                                <a class="admin-row-actions__edit" href="<?= url('/admin/mensajes/' . (int) $message['id']) ?>">
                                    Leer<span class="visually-hidden"> el mensaje de <?= e($message['name']) ?></span>
                                </a>

                                <?php // Marcar es escribir, así que va por POST y con token. ?>
                                <form method="post" action="<?= url('/admin/mensajes/' . (int) $message['id'] . '/leido') ?>">
                                    <input type="hidden" name="_token" value="<?= e($token) ?>">
                                    <button class="admin-row-actions__toggle" type="submit">
                                        <?= (int) $message['is_read'] === 1 ? 'Marcar nuevo' : 'Marcar leído' ?>
                                    </button>
                                </form>

                                <a class="admin-row-actions__delete" href="<?= url('/admin/mensajes/' . (int) $message['id'] . '/eliminar') ?>">
                                    Borrar<span class="visually-hidden"> el mensaje de <?= e($message['name']) ?></span>
                                </a>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
