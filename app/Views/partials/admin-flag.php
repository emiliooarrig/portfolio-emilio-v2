<?php
/**
 * Píldora de estado para las tablas: una bandera de la base, legible.
 *
 * Se lee de un vistazo por color, pero nunca sólo por color — el texto dice
 * lo mismo, que es lo que ve quien no distingue un color de otro.
 *
 * @var bool   $on
 * @var string $yes
 * @var string $no
 * @var bool   $quiet  true = el "no" es lo normal y no merece resaltarse
 */
$quiet = $quiet ?? false;
?>
<span class="pill <?= $on ? ($quiet ? 'pill--accent' : 'pill--on') : ($quiet ? 'pill--mute' : 'pill--off') ?>">
    <?= e($on ? $yes : $no) ?>
</span>
