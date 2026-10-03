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
    <?php // Aparece por máscara de línea: el span exterior recorta y el
          // interior sube (animations.scss). Sin JS se ve tal cual. ?>
    <h2 class="section__title" data-reveal="line"><span class="reveal-line"><span class="reveal-line__inner"><?= e($title) ?></span></span></h2>

    <?php if ($lead): ?>
        <p class="section__lead" data-reveal style="--i: 1"><?= e($lead) ?></p>
    <?php endif; ?>
</header>
