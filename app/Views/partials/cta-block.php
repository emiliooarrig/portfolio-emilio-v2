<?php
/**
 * Bloque CTA reutilizable.
 *
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $text
 * @var array{label: string, href: string}|null $primary
 * @var array{label: string, href: string}|null $secondary
 * @var string|null $variant   inline | panel | footer
 */

$variant   = $variant   ?? 'panel';
$eyebrow   = $eyebrow   ?? null;
$text      = $text      ?? null;
$primary   = $primary   ?? null;
$secondary = $secondary ?? null;
?>
<div class="cta-block cta-block--<?= e($variant) ?>">
    <div class="cta-block__copy">
        <?php if ($eyebrow): ?>
            <p class="eyebrow mono"><?= e($eyebrow) ?></p>
        <?php endif; ?>

        <h2 class="cta-block__title"><?= e($title) ?></h2>

        <?php if ($text): ?>
            <p class="cta-block__text"><?= e($text) ?></p>
        <?php endif; ?>
    </div>

    <div class="cta-block__actions">
        <?php if ($primary): ?>
            <a class="cta cta--primary" href="<?= url($primary['href']) ?>"><?= e($primary['label']) ?></a>
        <?php endif; ?>
        <?php if ($secondary): ?>
            <a class="cta cta--secondary" href="<?= url($secondary['href']) ?>"><?= e($secondary['label']) ?></a>
        <?php endif; ?>
    </div>
</div>
