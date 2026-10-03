<?php
/**
 * Motivo generativo: la firma visual del sitio y la vista previa abstracta
 * de cada proyecto (no hay capturas que mostrar).
 *
 * Un hash estable del `seed` elige una de cuatro composiciones —barras,
 * retícula de puntos, anillos o líneas— y sus parámetros. El mismo seed
 * produce siempre la misma imagen. Los colores salen de variables CSS
 * (`_pattern.scss`), así que cambian con el tema.
 *
 * @var string $seed     slug del proyecto, nombre completo, etc.
 * @var string $variant  preview (320×220) | hero (cuadrado) | cover (16:10)
 */

$seed    = (string) ($seed ?? 'pattern');
$variant = $variant ?? 'preview';
$variant = in_array($variant, ['preview', 'hero', 'cover'], true) ? $variant : 'preview';

[$w, $h] = match ($variant) {
    'hero'  => [600, 600],
    'cover' => [800, 500],
    default => [320, 220],
};

// Generador pseudoaleatorio determinista (LCG de 31 bits): cada llamada
// devuelve un número en [0, 1) y la secuencia depende sólo del seed.
$state = crc32($seed) & 0x7fffffff;
$rand  = static function () use (&$state): float {
    $state = ($state * 1103515245 + 12345) & 0x7fffffff;

    return $state / 0x80000000;
};
$between = static fn (float $min, float $max): float => $min + ($max - $min) * $rand();
$n       = static fn (float $value): string => rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');

$composition = crc32(strrev($seed)) % 4;
$pad         = $variant === 'hero' ? 24 : round(min($w, $h) * 0.12);
$innerW      = $w - 2 * $pad;
$innerH      = $h - 2 * $pad;
$shapes      = [];

switch ($composition) {
    // 1. Barras: columnas de alturas distintas, como una gráfica sin ejes.
    case 0:
        $count = (int) round($between(7, 14));
        $slot  = $innerW / $count;
        $bar   = $slot * $between(0.5, 0.78);
        $level = $between(0.3, 0.7);
        $peak  = (int) floor($rand() * $count);
        $fromTop = $rand() < 0.3;

        for ($i = 0; $i < $count; $i++) {
            // Paseo aleatorio suave: una serie, no ruido.
            $level = max(0.12, min(1, $level + $between(-0.28, 0.28)));
            $height = $i === $peak ? $innerH : $innerH * $level;
            $x = $pad + $i * $slot + ($slot - $bar) / 2;
            $y = $fromTop ? $pad : $pad + $innerH - $height;
            $class = $i === $peak ? 'pattern__k' : ($rand() < 0.18 ? 'pattern__w' : 'pattern__c');
            $shapes[] = sprintf('<rect class="%s" x="%s" y="%s" width="%s" height="%s" rx="%s"/>', $class, $n($x), $n($y), $n($bar), $n($height), $n(min(6, $bar / 4)));
        }
        break;

    // 2. Retícula de puntos: algunos crecen o se rellenan.
    case 1:
        $cols = (int) round($between(7, 12));
        $gap  = $innerW / ($cols - 1);
        $rows = max(3, (int) floor($innerH / $gap) + 1);
        $r    = $gap * $between(0.08, 0.14);

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $cx = $pad + $col * $gap;
                $cy = $pad + $row * $gap + ($innerH - ($rows - 1) * $gap) / 2;
                $roll = $rand();

                if ($roll < 0.12) {
                    $shapes[] = sprintf('<circle class="pattern__c" cx="%s" cy="%s" r="%s"/>', $n($cx), $n($cy), $n($r * $between(2.2, 3.6)));
                } elseif ($roll < 0.18) {
                    $shapes[] = sprintf('<circle class="pattern__ring" cx="%s" cy="%s" r="%s"/>', $n($cx), $n($cy), $n($r * 2.4));
                } elseif ($roll < 0.22) {
                    $shapes[] = sprintf('<circle class="pattern__k" cx="%s" cy="%s" r="%s"/>', $n($cx), $n($cy), $n($r * 1.6));
                } else {
                    $shapes[] = sprintf('<circle class="pattern__dot" cx="%s" cy="%s" r="%s"/>', $n($cx), $n($cy), $n($r));
                }
            }
        }
        break;

    // 3. Anillos concéntricos, con uno o dos arcos cortados.
    case 2:
        $cx    = $pad + $innerW * $between(0.3, 0.7);
        $cy    = $pad + $innerH * $between(0.3, 0.7);
        $count = (int) round($between(4, 8));
        $max   = max($innerW, $innerH) * $between(0.55, 0.8);
        $step  = $max / $count;
        $cuts  = [(int) floor($rand() * $count), (int) floor($rand() * $count)];
        $width = max(2, $step * $between(0.18, 0.32));

        for ($i = 1; $i <= $count; $i++) {
            $radius = $step * $i;
            $class  = $i === $count - 1 ? 'pattern__stroke-k' : 'pattern__stroke-c';
            $attrs  = '';

            if (in_array($i - 1, $cuts, true)) {
                $length = 2 * M_PI * $radius;
                $attrs  = sprintf(' stroke-dasharray="%s %s" transform="rotate(%s %s %s)"', $n($length * $between(0.55, 0.8)), $n($length), $n($between(0, 360)), $n($cx), $n($cy));
            }

            $shapes[] = sprintf('<circle class="%s" cx="%s" cy="%s" r="%s" stroke-width="%s"%s/>', $class, $n($cx), $n($cy), $n($radius), $n($width), $attrs);
        }

        $shapes[] = sprintf('<circle class="pattern__c" cx="%s" cy="%s" r="%s"/>', $n($cx), $n($cy), $n($step * 0.45));
        break;

    // 4. Líneas paralelas con un desvío o quiebre.
    default:
        $count  = (int) round($between(11, 18));
        $angle  = $between(-28, 28);
        $span   = hypot($w, $h);
        $gap    = $span / $count;
        $width  = max(2, $gap * $between(0.16, 0.3));
        $broken = (int) floor($between(0.25, 0.75) * $count);
        $group  = [];

        for ($i = 0; $i < $count; $i++) {
            $y = -$span / 2 + $i * $gap + $gap / 2;

            if ($i === $broken) {
                $kink = $between(-0.25, 0.25) * $span;
                $lift = $gap * $between(0.8, 1.6) * ($rand() < 0.5 ? -1 : 1);
                $group[] = sprintf('<polyline class="pattern__stroke-k" points="%s,%s %s,%s %s,%s %s,%s" stroke-width="%s"/>', $n(-$span), $n($y), $n($kink - $gap), $n($y), $n($kink + $gap), $n($y + $lift), $n($span), $n($y + $lift), $n($width));
            } else {
                $group[] = sprintf('<line class="pattern__stroke-c" x1="%s" y1="%s" x2="%s" y2="%s" stroke-width="%s"/>', $n(-$span), $n($y), $n($span), $n($y), $n($width));
            }
        }

        $shapes[] = sprintf('<g transform="translate(%s %s) rotate(%s)">%s</g>', $n($w / 2), $n($h / 2), $n($angle), implode('', $group));
        break;
}
?>
<svg class="pattern pattern--<?= e($variant) ?>" viewBox="0 0 <?= $w ?> <?= $h ?>" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
    <?php if ($variant !== 'hero'): ?>
        <rect class="pattern__bg" width="<?= $w ?>" height="<?= $h ?>"/>
    <?php endif; ?>
    <g class="pattern__art"><?= implode('', $shapes) ?></g>
</svg>
