<?php
/**
 * La última columna de cada tabla: editar y borrar.
 *
 * Los dos son enlaces, nunca botones que actúen solos, y dicen lo que hacen
 * con un ícono: en una tabla, la acción se reconoce antes de leerla. El
 * nombre de la fila viaja en el `aria-label`, que es lo que anuncia un lector
 * de pantalla y lo que sale en el `title` al pasar por encima.
 *
 * Borrar apunta a su pantalla de confirmación, que sigue siendo la salida
 * cuando no hay JavaScript. Con JavaScript, `alerts.js` intercepta el clic y
 * pregunta con la alerta del sitio, sin salir del listado — de ahí los
 * `data-confirm-*`, que son lo que esa alerta lee.
 *
 * @var string $edit
 * @var string $delete
 * @var string $name         nombre de la fila: qué se edita o se borra
 * @var string $confirmText  qué se lleva por delante el borrado
 * @var string $editLabel    alias antiguo de `name`
 */
$name        = trim((string) ($name ?? ($editLabel ?? '')));
$confirmText = trim((string) ($confirmText ?? '')) ?: 'Se borra para siempre. No hay deshacer.';

$about       = $name === '' ? '' : ' «' . $name . '»';
$confirmName = $name === '' ? '¿Borrar esta fila?' : '¿Borrar «' . $name . '»?';
?>
<span class="admin-row-actions">
    <a class="admin-row-actions__action admin-row-actions__action--edit"
       href="<?= url($edit) ?>"
       title="Editar<?= e($about) ?>"
       aria-label="Editar<?= e($about) ?>">
        <?= partial('admin-icon', ['name' => 'pencil']) ?>
    </a>

    <a class="admin-row-actions__action admin-row-actions__action--delete"
       href="<?= url($delete) ?>"
       title="Borrar<?= e($about) ?>"
       aria-label="Borrar<?= e($about) ?>"
       data-confirm
       data-confirm-title="<?= e($confirmName) ?>"
       data-confirm-text="<?= e($confirmText) ?>">
        <?= partial('admin-icon', ['name' => 'trash']) ?>
    </a>
</span>
