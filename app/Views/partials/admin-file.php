<?php
/**
 * Campo de archivo del panel: sube una foto o un documento.
 *
 * Quien edita ya no escribe rutas — elige un archivo y el backend decide
 * dónde vive. Por eso el campo enseña primero lo que hay guardado (miniatura
 * o ficha del documento) y sólo después ofrece reemplazarlo o quitarlo.
 *
 * @var string $name     nombre del input de archivo
 * @var string $label
 * @var string $current  ruta pública de lo que hay guardado ('' si no hay)
 * @var string $accept   tipos que ofrece el diálogo del sistema
 * @var string $formats  "JPG, PNG, WEBP o AVIF"
 * @var string $limit    "4 MB"
 * @var string $preview  image|doc
 * @var string $error
 * @var string $remove   nombre de la casilla para quitar el actual
 */

$current = trim((string) ($current ?? ''));
$accept  = $accept  ?? '';
$formats = $formats ?? '';
$limit   = $limit   ?? '';
$preview = $preview ?? 'doc';
$error   = $error   ?? '';
$remove  = $remove  ?? '';

$id   = 'f-' . str_replace('_', '-', $name);
$hint = trim($formats . ($formats !== '' && $limit !== '' ? ' · hasta ' . $limit : $limit));

$described = [$id . '-hint'];
if ($error !== '') {
    $described[] = $id . '-error';
}
?>
<div class="form__row">
    <label class="form__label meta" for="<?= e($id) ?>">
        <?= e($label) ?>
        <span class="form__optional">opcional</span>
    </label>

    <?php if ($current !== ''): ?>
        <div class="admin-file__current">
            <?php if ($preview === 'image'): ?>
                <img class="admin-file__thumb" src="<?= e(url($current)) ?>" alt="" width="64" height="64">
            <?php else: ?>
                <span class="admin-file__thumb admin-file__thumb--doc" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"></path>
                        <path d="M14 3v5h5"></path>
                    </svg>
                </span>
            <?php endif; ?>

            <span class="admin-file__meta">
                <span class="admin-file__name"><?= e(basename($current)) ?></span>
                <a class="admin-file__open" href="<?= e(url($current)) ?>" target="_blank" rel="noopener">Ver el archivo actual</a>
            </span>
        </div>
    <?php endif; ?>

    <input class="form__input admin-file__input<?= $error !== '' ? ' is-invalid' : '' ?>"
           type="file"
           id="<?= e($id) ?>"
           name="<?= e($name) ?>"
           accept="<?= e($accept) ?>"
           <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
           aria-describedby="<?= e(implode(' ', $described)) ?>">

    <p class="form__hint" id="<?= e($id) ?>-hint">
        <?= e($hint) ?><?= $current !== '' ? ' · al guardar, el archivo anterior se borra del servidor.' : '' ?>
    </p>

    <?php if ($error !== ''): ?>
        <p class="form__error" id="<?= e($id) ?>-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($current !== '' && $remove !== ''): ?>
        <label class="admin-check admin-file__remove" for="<?= e($id) ?>-remove">
            <input class="admin-check__box" type="checkbox" id="<?= e($id) ?>-remove" name="<?= e($remove) ?>" value="1">
            <span class="admin-check__label">Quitar el archivo actual (se borra del servidor)</span>
        </label>
    <?php endif; ?>
</div>
