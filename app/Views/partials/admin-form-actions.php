<?php
/**
 * Pie de todo formulario del panel: guardar, cancelar y —si la fila ya
 * existe— borrar.
 *
 * Borrar es un enlace y no un botón dentro del formulario: lleva a su
 * pantalla de confirmación, así que no puede borrar por sí solo ni arrastrar
 * lo que estuviera escrito sin guardar.
 *
 * @var string $label      texto del botón principal
 * @var string $back       a dónde vuelve «Cancelar»
 * @var string $deleteUrl  vacío en un alta: todavía no hay nada que borrar
 */
$deleteUrl = $deleteUrl ?? '';
?>
<div class="admin-form__actions">
    <button class="cta cta--primary" type="submit"><?= e($label) ?></button>

    <a class="cta cta--ghost" href="<?= url($back) ?>">Cancelar</a>

    <?php if ($deleteUrl !== ''): ?>
        <a class="admin-form__delete" href="<?= url($deleteUrl) ?>">Borrar</a>
    <?php endif; ?>
</div>
