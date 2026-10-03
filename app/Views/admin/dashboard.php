<?php
/**
 * Resumen del panel: cuánto hay de cada cosa y lo último que llegó.
 *
 * @var array<int, array<string, mixed>> $summary  tarjetas por sección
 * @var array<int, array<string, mixed>> $recent   últimos mensajes
 * @var int $unread
 * @var array<string, mixed>|null $admin
 */
?>
<?= partial('admin-head', [
    'title' => 'Hola, ' . ($admin['display_name'] ?? ''),
    'lead'  => 'Esto es lo que la landing está publicando ahora mismo.',
    'meta'  => $unread === 0
        ? 'Sin mensajes pendientes'
        : plural($unread, 'mensaje sin leer', 'mensajes sin leer'),
]) ?>

<ul class="admin-stats">
    <?php foreach ($summary as $card): ?>
        <li>
            <a class="admin-stat" href="<?= url($card['path']) ?>">
                <span class="admin-stat__label"><?= e($card['label']) ?></span>
                <span class="admin-stat__value meta"><?= (int) $card['total'] ?></span>
                <span class="admin-stat__detail"><?= e($card['detail']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<section class="admin-panel">
    <div class="admin-panel__head">
        <h2 class="admin-panel__title">Últimos mensajes</h2>
        <a class="link-arrow" href="<?= url('/admin/mensajes') ?>">Ver todos</a>
    </div>

    <?php if ($recent === []): ?>
        <p class="admin-empty">Nadie ha escrito todavía por el formulario de contacto.</p>
    <?php else: ?>
        <div class="admin-table__wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Recibido</th>
                        <th scope="col">De</th>
                        <th scope="col">Asunto</th>
                        <th scope="col">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $message): ?>
                        <tr>
                            <td class="meta"><?= e(date('d/m/Y H:i', strtotime((string) $message['created_at']))) ?></td>
                            <td>
                                <strong><?= e($message['name']) ?></strong>
                                <span class="admin-table__sub"><?= e($message['email']) ?></span>
                            </td>
                            <td><?= e($message['subject'] ?: '—') ?></td>
                            <td>
                                <?= partial('admin-flag', [
                                    'on'    => (int) $message['is_read'] === 1,
                                    'yes'   => 'Leído',
                                    'no'    => 'Nuevo',
                                    'quiet' => false,
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<p class="admin-note">
    Por ahora el panel es de lectura: muestra el estado real de la base, incluido
    lo que está oculto en la landing. La edición se construye sobre estas mismas
    pantallas.
</p>
