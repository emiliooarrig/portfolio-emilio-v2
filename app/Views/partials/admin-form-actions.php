<?php
/**
 * Pie de todo formulario del panel: guardar, cancelar y —si la fila ya
 * existe— borrar.
 *
 * Borrar es un enlace y no un botón dentro del formulario: así no puede
 * borrar por sí solo ni arrastrar lo que estuviera escrito sin guardar. Con
 * JavaScript pregunta con la alerta del sitio; sin él, lleva a su pantalla de
 * confirmación, que es a donde apunta.
 *
 * @var string $label       texto del botón principal
 * @var string $back        a dónde vuelve «Cancelar»
 * @var string $deleteUrl   vacío en un alta: todavía no hay nada que borrar
 * @var string $deleteName  nombre de la fila, para la pregunta
 * @var string $deleteText  qué se lleva por delante el borrado
 */
$deleteUrl  = $deleteUrl ?? '';
$deleteName = trim((string) ($deleteName ?? ''));
$deleteText = trim((string) ($deleteText ?? '')) ?: 'Se borra para siempre. No hay deshacer.';
?>
<div class="admin-form__actions">
    <button class="cta cta--primary" type="submit"><?= e($label) ?></button>

    <a class="cta cta--ghost" href="<?= url($back) ?>">Cancelar</a>

    <?php if ($deleteUrl !== ''): ?>
        <a class="admin-form__delete"
           href="<?= url($deleteUrl) ?>"
           data-confirm
           data-confirm-title="<?= e($deleteName === '' ? '¿Borrar?' : '¿Borrar «' . $deleteName . '»?') ?>"
           data-confirm-text="<?= e($deleteText) ?>">
            <?= partial('admin-icon', ['name' => 'trash', 'size' => 15]) ?>
            Borrar
        </a>
    <?php endif; ?>
</div>
