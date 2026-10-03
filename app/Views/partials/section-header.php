<?php
/**
 * Encabezado de sección: el riel izquierdo del patrón riel + contenido.
 *
 * El único <h1> del sitio es el nombre en el hero: aquí van <h2>.
 *
 * @var string      $title
 * @var string|null $lead
 */

$lead = $lead ?? null;
?>
<header class="section__head">
    <h2 class="section__title"><?= e($title) ?></h2>

    <?php if ($lead): ?>
        <p class="section__lead"><?= e($lead) ?></p>
    <?php endif; ?>
</header>
