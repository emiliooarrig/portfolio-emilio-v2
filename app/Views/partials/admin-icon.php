<?php
/**
 * Ícono suelto del panel, en el mismo trazo que el resto del sitio.
 *
 * Vive aquí y no en cada vista porque las acciones de una fila —editar,
 * borrar, leer— se repiten en las seis tablas del panel: un solo dibujo por
 * acción, y todas iguales.
 *
 *     <?= partial('admin-icon', ['name' => 'trash']) ?>
 *
 * @var string $name
 * @var int    $size
 */

$icons = [
    'pencil' => '<path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17v3Z"></path><path d="M14.5 7.5l2 2"></path>',
    'trash'  => '<path d="M4 7h16"></path><path d="M9.5 7V5.2A1.2 1.2 0 0 1 10.7 4h2.6a1.2 1.2 0 0 1 1.2 1.2V7"></path><path d="M6.5 7l.8 12a2 2 0 0 0 2 1.9h5.4a2 2 0 0 0 2-1.9l.8-12"></path><path d="M10.5 11v6M13.5 11v6"></path>',
    'eye'    => '<path d="M2.5 12S6 6.5 12 6.5 21.5 12 21.5 12 18 17.5 12 17.5 2.5 12 2.5 12Z"></path><circle cx="12" cy="12" r="3"></circle>',
    'check'  => '<circle cx="12" cy="12" r="9"></circle><path d="m8.2 12.4 2.6 2.6 5-5.4"></path>',
    'undo'   => '<path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"></path><path d="M3.5 4v5h5"></path>',
];

$name = (string) ($name ?? '');
$size = (int) ($size ?? 17);

if (! isset($icons[$name])) {
    return;
}
?>
<svg viewBox="0 0 24 24" width="<?= $size ?>" height="<?= $size ?>" fill="none" stroke="currentColor"
     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$name] ?></svg>
