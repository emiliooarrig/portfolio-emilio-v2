<?php
/**
 * Un mensaje del formulario de contacto, entero.
 *
 * La tabla recorta el texto para poder barrerla; aquí se lee completo y tal
 * como llegó — ni se edita ni se reformatea.
 *
 * @var array<string, mixed> $message
 * @var string               $back
 * @var string               $token
 */

$received = (int) strtotime((string) $message['created_at']);
$subject  = (string) ($message['subject'] ?: 'Sin asunto');
?>
<?= partial('admin-head', [
    'title' => $subject,
    'lead'  => 'De ' . $message['name'] . ' · ' . date('d/m/Y \a \l\a\s H:i', $received),
    'meta'  => (int) $message['is_read'] === 1 ? 'Leído' : 'Nuevo',
]) ?>

<article class="admin-message">

    <dl class="admin-message__meta">
        <div class="admin-confirm__row">
            <dt class="admin-confirm__key meta">Responder a</dt>
            <dd class="admin-confirm__val">
                <a href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $subject) ?>">
                    <?= e($message['email']) ?>
                </a>
            </dd>
        </div>

        <div class="admin-confirm__row">
            <dt class="admin-confirm__key meta">Origen</dt>
            <dd class="admin-confirm__val meta"><?= e($message['ip_address'] ?: '—') ?></dd>
        </div>
    </dl>

    <div class="admin-message__body prose">
        <?= paragraphs((string) $message['message']) ?>
    </div>

    <div class="admin-message__actions">
        <a class="cta cta--secondary" href="<?= url($back) ?>">Volver a la bandeja</a>

        <?php // Marcarlo de vuelta como nuevo: útil cuando se abre por error
              // y se quiere contestar con calma más tarde. ?>
        <form method="post" action="<?= url($back . '/' . (int) $message['id'] . '/leido') ?>">
            <input type="hidden" name="_token" value="<?= e($token) ?>">
            <button class="cta cta--ghost" type="submit">
                <?= (int) $message['is_read'] === 1 ? 'Marcar como nuevo' : 'Marcar como leído' ?>
            </button>
        </form>

        <a class="admin-form__delete" href="<?= url($back . '/' . (int) $message['id'] . '/eliminar') ?>">Borrar</a>
    </div>

</article>
