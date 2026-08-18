<?php
/**
 * La última columna de cada tabla: editar y borrar.
 *
 * Los dos son enlaces, nunca botones que actúen solos. Borrar lleva a su
 * pantalla de confirmación — desde un listado, con las filas una encima de
 * otra, es demasiado fácil pulsar la que no era.
 *
 * @var string $edit
 * @var string $delete
 * @var string $editLabel  para lectores de pantalla: qué fila es esta
 */
$editLabel = $editLabel ?? '';
$suffix    = $editLabel === '' ? '' : ' ' . $editLabel;
?>
<span class="admin-row-actions">
    <a class="admin-row-actions__edit" href="<?= url($edit) ?>">
        Editar<span class="visually-hidden"><?= e($suffix) ?></span>
    </a>
    <a class="admin-row-actions__delete" href="<?= url($delete) ?>">
        Borrar<span class="visually-hidden"><?= e($suffix) ?></span>
    </a>
</span>
