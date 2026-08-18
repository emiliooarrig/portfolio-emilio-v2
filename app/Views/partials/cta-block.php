<?php
/**
 * Bloque CTA reutilizable.
 *
 * Los href que empiezan con "#" son anclas del mismo scroll y se dejan tal
 * cual; el resto pasa por url() para respetar el base_path.
 *
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $text
 * @var array{label: string, href: string, icon?: string}|null $primary
 * @var array{label: string, href: string, icon?: string}|null $secondary
 * @var string|null $variant   inline | panel | footer | modal
 */

$variant   = $variant   ?? 'panel';
$eyebrow   = $eyebrow   ?? null;
$text      = $text      ?? null;
$primary   = $primary   ?? null;
$secondary = $secondary ?? null;

$href = static fn (string $path): string => str_starts_with($path, '#') ? $path : url($path);

// Sólo las variantes con caja reciben el hover iluminado: footer y modal
// no tienen contenedor que encender.
$hoverable = in_array($variant, ['inline', 'panel'], true) ? ' bento-card--hoverable' : '';
?>
<div class="cta-block cta-block--<?= e($variant) ?><?= $hoverable ?>">
    <div class="cta-block__copy">
        <?php if ($eyebrow): ?>
            <p class="eyebrow meta"><?= e($eyebrow) ?></p>
        <?php endif; ?>

        <h2 class="cta-block__title"><?= e($title) ?></h2>

        <?php if ($text): ?>
            <p class="cta-block__text"><?= e($text) ?></p>
        <?php endif; ?>
    </div>

    <div class="cta-block__actions">
        <?php if ($primary): ?>
            <a class="cta cta--primary" href="<?= e($href($primary['href'])) ?>">
                <?= e($primary['label']) ?>
                <?php if (! empty($primary['icon'])): ?>
                    <span class="cta__icon" aria-hidden="true"><?= e($primary['icon']) ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($secondary): ?>
            <a class="cta cta--secondary" href="<?= e($href($secondary['href'])) ?>">
                <?= e($secondary['label']) ?>
                <?php if (! empty($secondary['icon'])): ?>
                    <span class="cta__icon" aria-hidden="true"><?= e($secondary['icon']) ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>
</div>
