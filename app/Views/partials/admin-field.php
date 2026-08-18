<?php
/**
 * Un campo del formulario del panel.
 *
 * Todos los formularios se arman con esta pieza, así que el error, la pista
 * y el "opcional" salen siempre en el mismo sitio y con el mismo tono. Los
 * campos reutilizan `.form*` del formulario de contacto: los del panel y los
 * del sitio son los mismos, y deben seguir siéndolo.
 *
 * @var string                $name
 * @var string                $label
 * @var mixed                 $value
 * @var string                $type         text|textarea|number|date|url|email|select|checkbox
 * @var string                $error        mensaje del intento anterior
 * @var string                $hint         una línea de ayuda bajo el campo
 * @var bool                  $required
 * @var array<string, string> $options      sólo para select: valor => etiqueta
 * @var string                $placeholder
 * @var int                   $rows         sólo para textarea
 */

$type        = $type        ?? 'text';
$error       = $error       ?? '';
$hint        = $hint        ?? '';
$required    = $required    ?? false;
$options     = $options     ?? [];
$placeholder = $placeholder ?? '';
$rows        = $rows        ?? 4;
$value       = $value       ?? '';

$id      = 'f-' . str_replace('_', '-', $name);
$invalid = $error !== '' ? ' is-invalid' : '';

// Lo que describe al campo, para que un lector de pantalla lo anuncie junto
// con él y no como texto suelto después.
$described = [];
if ($hint !== '') {
    $described[] = $id . '-hint';
}
if ($error !== '') {
    $described[] = $id . '-error';
}
$describedBy = $described === [] ? '' : ' aria-describedby="' . implode(' ', $described) . '"';
?>

<?php if ($type === 'checkbox'): ?>

    <div class="form__row form__row--check">
        <label class="admin-check" for="<?= e($id) ?>">
            <input class="admin-check__box"
                   type="checkbox"
                   id="<?= e($id) ?>"
                   name="<?= e($name) ?>"
                   value="1"
                   <?= (int) $value === 1 ? 'checked' : '' ?>
                   <?= $describedBy ?>>
            <span class="admin-check__label"><?= e($label) ?></span>
        </label>

        <?php if ($hint !== ''): ?>
            <p class="form__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="form__error" id="<?= e($id) ?>-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>
    </div>

<?php else: ?>

    <div class="form__row">
        <label class="form__label meta" for="<?= e($id) ?>">
            <?= e($label) ?>
            <?php if (! $required): ?>
                <span class="form__optional">opcional</span>
            <?php endif; ?>
        </label>

        <?php if ($type === 'textarea'): ?>
            <textarea class="form__input form__input--area<?= $invalid ?>"
                      id="<?= e($id) ?>"
                      name="<?= e($name) ?>"
                      rows="<?= (int) $rows ?>"
                      placeholder="<?= e($placeholder) ?>"
                      <?= $required ? 'required' : '' ?>
                      <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                      <?= $describedBy ?>><?= e((string) $value) ?></textarea>

        <?php elseif ($type === 'select'): ?>
            <select class="form__input form__input--select<?= $invalid ?>"
                    id="<?= e($id) ?>"
                    name="<?= e($name) ?>"
                    <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                    <?= $describedBy ?>>
                <?php foreach ($options as $optionValue => $optionLabel): ?>
                    <option value="<?= e((string) $optionValue) ?>"
                        <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>>
                        <?= e($optionLabel) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        <?php else: ?>
            <input class="form__input<?= $invalid ?>"
                   type="<?= e($type) ?>"
                   id="<?= e($id) ?>"
                   name="<?= e($name) ?>"
                   value="<?= e((string) $value) ?>"
                   placeholder="<?= e($placeholder) ?>"
                   <?= $required ? 'required' : '' ?>
                   <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                   <?= $describedBy ?>>
        <?php endif; ?>

        <?php if ($hint !== ''): ?>
            <p class="form__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="form__error" id="<?= e($id) ?>-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>
    </div>

<?php endif; ?>
