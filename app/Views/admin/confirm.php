<?php
/**
 * Confirmación de borrado, compartida por todas las secciones.
 *
 * Es una pantalla y no un diálogo del navegador porque el panel va sin JS —
 * y porque un borrado merece que se vea qué se lleva por delante antes de
 * pulsar. El botón que borra es POST con token; llegar aquí por GET no
 * borra nada.
 *
 * @var string                $title
 * @var string                $lead
 * @var array<string, string> $detail   pares que describen lo que se va
 * @var string                $action   a dónde se envía el POST
 * @var string                $back
 * @var string                $hint     la salida menos drástica, si la hay
 * @var string                $token
 */
$hint = $hint ?? '';
?>
<?= partial('admin-head', [
    'title' => $title,
    'lead'  => $lead,
]) ?>

<div class="admin-confirm">

    <?php if ($detail !== []): ?>
        <dl class="admin-confirm__detail">
            <?php foreach ($detail as $label => $value): ?>
                <div class="admin-confirm__row">
                    <dt class="admin-confirm__key meta"><?= e((string) $label) ?></dt>
                    <dd class="admin-confirm__val"><?= e((string) $value) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <div class="admin-confirm__actions">
        <form method="post" action="<?= url($action) ?>">
            <input type="hidden" name="_token" value="<?= e($token) ?>">
            <button class="cta cta--danger" type="submit">Sí, borrar</button>
        </form>

        <a class="cta cta--secondary" href="<?= url($back) ?>">No, volver</a>
    </div>

    <?php if ($hint !== ''): ?>
        <p class="admin-confirm__hint"><?= e($hint) ?></p>
    <?php endif; ?>

</div>
