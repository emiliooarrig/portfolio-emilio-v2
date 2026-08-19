<?php
/**
 * Alerta del sitio: lo que el backend tiene que decir después de una acción.
 *
 * Es la única pieza que anuncia resultados — guardado, borrado, error o
 * aviso —, tanto en la landing como en el panel. Quien quiera lanzar una
 * sólo pasa por aquí:
 *
 *     <?= partial('alert', ['type' => 'success', 'text' => 'Perfil guardado.']) ?>
 *
 * Se dibuja como un <dialog> ya abierto, así que aparece con la página y no
 * depende de JavaScript: el botón la cierra con `method="dialog"`, que es
 * nativo. `alerts.js` sólo la asciende a modal —foco atrapado y Escape— y
 * cierra sola las de éxito, que no necesitan respuesta.
 *
 * @var string $type    success|error|warning|info
 * @var string $text    el mensaje; sin él no se dibuja nada
 * @var string $title   opcional: si falta, el que corresponde al tipo
 * @var string $confirm  opcional: etiqueta del botón
 * @var string $cancel   opcional: si viene, la alerta pregunta en vez de avisar
 * @var string $template opcional: envuelve el diálogo en un <template> para que
 *                       lo clone `alerts.js` cuando haga falta preguntar algo
 */

$text     = trim((string) ($text ?? ''));
$template = trim((string) ($template ?? ''));

// Sin mensaje no hay nada que anunciar. La plantilla es la excepción: nace
// vacía y el texto se lo pone quien la clone.
if ($text === '' && $template === '') {
    return;
}

/** Cada tipo trae su tono, su título por defecto y su ícono. */
$kinds = [
    'success' => [
        'title' => 'Listo',
        'icon'  => '<circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path>',
    ],
    'error' => [
        'title' => 'Algo no salió',
        'icon'  => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7.6v5"></path><path d="M12 16.2h.01"></path>',
    ],
    'warning' => [
        'title' => 'Atención',
        'icon'  => '<path d="M12 3.8 2.9 19.6h18.2L12 3.8Z"></path><path d="M12 10v4"></path><path d="M12 17h.01"></path>',
    ],
    'info' => [
        'title' => 'Aviso',
        'icon'  => '<circle cx="12" cy="12" r="9"></circle><path d="M12 11.4v5"></path><path d="M12 7.8h.01"></path>',
    ],
];

$type = (string) ($type ?? 'info');
$kind = $kinds[$type] ?? $kinds['info'];

$title   = trim((string) ($title ?? '')) ?: $kind['title'];
$cancel  = trim((string) ($cancel ?? ''));
$confirm = trim((string) ($confirm ?? '')) ?: ($cancel === '' ? 'Entendido' : 'Sí, borrar');

// Puede haber más de una alerta en la página: cada una necesita sus propios
// ids para que aria-labelledby no apunte a la de al lado.
$GLOBALS['__alert_seq'] = ($GLOBALS['__alert_seq'] ?? 0) + 1;
$id = 'alert-' . $GLOBALS['__alert_seq'];
?>
<?php if ($template !== ''): ?><template data-alert-template="<?= e($template) ?>"><?php endif; ?>
<dialog class="alert alert--<?= e($type) ?>"
        id="<?= e($id) ?>"
        data-alert
        data-alert-type="<?= e($type) ?>"
        role="alertdialog"
        aria-labelledby="<?= e($id) ?>-title"
        aria-describedby="<?= e($id) ?>-text"
        <?= $template === '' ? 'open' : '' ?>>

    <div class="alert__panel">
        <span class="alert__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <?= $kind['icon'] ?>
            </svg>
        </span>

        <h2 class="alert__title" id="<?= e($id) ?>-title"><?= e($title) ?></h2>
        <p class="alert__text" id="<?= e($id) ?>-text"><?= e($text) ?></p>

        <?php // method="dialog": los botones cierran el diálogo sin una línea de JS.
              // Cuando la alerta pregunta, el que acepta no cierra y ya está: lo
              // recoge `alerts.js`, que es quien sabe qué hacer con la respuesta. ?>
        <form class="alert__actions" method="dialog">
            <?php if ($cancel !== ''): ?>
                <button class="cta cta--secondary" type="submit" value="cancel" data-alert-dismiss autofocus>
                    <?= e($cancel) ?>
                </button>
                <button class="cta cta--danger" type="submit" value="accept" data-alert-accept>
                    <?= e($confirm) ?>
                </button>
            <?php else: ?>
                <button class="cta cta--primary" type="submit" data-alert-dismiss autofocus>
                    <?= e($confirm) ?>
                </button>
            <?php endif; ?>
        </form>
    </div>
</dialog>
<?php if ($template !== ''): ?></template><?php endif; ?>
