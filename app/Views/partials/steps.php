<?php
/**
 * Secuencia numerada: los pasos de "Cómo trabajo" y el flujo de un proyecto.
 *
 * Es la única numeración del sitio, porque sólo aquí el orden es real. El
 * último paso lleva el punto verde: el resultado.
 *
 * @var array<int, array{label: string, description?: string|null}> $steps
 * @var string $variant  section (cinco columnas en escritorio) | compact (siempre vertical)
 *
 * Sólo la variante de sección aparece al hacer scroll: la compacta vive
 * dentro del modal, que llega por fetch y no se observa.
 */

$variant = in_array($variant ?? 'section', ['section', 'compact'], true) ? ($variant ?? 'section') : 'section';

if (empty($steps)) {
    return;
}
?>
<?php $reveal = $variant === 'section'; ?>
<ol class="steps steps--<?= e($variant) ?>"<?= $reveal ? ' data-reveal="draw"' : '' ?>>
    <?php foreach (array_values($steps) as $index => $step): ?>
        <li class="steps__item"<?= $reveal ? ' data-reveal style="--i: ' . $index . '"' : '' ?>>
            <span class="steps__num meta"><?= $index + 1 ?></span>
            <span class="steps__label"><?= e($step['label']) ?></span>
            <?php if (! empty($step['description'])): ?>
                <span class="steps__desc"><?= e($step['description']) ?></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
