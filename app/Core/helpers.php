<?php

/**
 * Funciones de apoyo disponibles en controladores y vistas.
 */

if (! function_exists('config')) {
    /**
     * Lee configuración con notación de punto: config('db.name').
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = require CONFIG_PATH . '/config.php';
        }

        $value = $config;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (! function_exists('e')) {
    /**
     * Escapa texto para salida HTML.
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('url')) {
    /**
     * Construye una URL absoluta respetando el base_path.
     */
    function url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.base_path', ''), '/');
        $path = '/' . ltrim($path, '/');

        return $base . ($path === '/' ? '/' : rtrim($path, '/'));
    }
}

if (! function_exists('asset')) {
    /**
     * URL de un asset estático, con cache-busting por fecha de modificación.
     */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = PUBLIC_PATH . '/assets/' . $path;
        $url  = url('/assets/' . $path);

        return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
    }
}

if (! function_exists('partial')) {
    /**
     * Incluye una vista parcial pasándole datos. Uso dentro de las vistas:
     * <?= partial('cta-block', ['title' => '…']) ?>
     *
     * @param array<string, mixed> $data
     */
    function partial(string $name, array $data = []): string
    {
        $file = APP_PATH . '/Views/partials/' . $name . '.php';

        if (! is_file($file)) {
            return '';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}

if (! function_exists('is_active')) {
    /**
     * ¿La ruta actual corresponde a esta sección de navegación?
     */
    function is_active(string $currentPath, string $routePath): bool
    {
        if ($routePath === '/') {
            return $currentPath === '/';
        }

        return $currentPath === $routePath || str_starts_with($currentPath, $routePath . '/');
    }
}

if (! function_exists('month_year')) {
    /**
     * "2024-01-15" → "ene 2024". Devuelve $fallback si la fecha es nula.
     */
    function month_year(?string $date, string $fallback = 'Actual'): string
    {
        if (empty($date)) {
            return $fallback;
        }

        static $months = [
            1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun',
            7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
        ];

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $fallback;
        }

        return $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

if (! function_exists('date_range')) {
    /**
     * Rango legible para el timeline de experiencia.
     */
    function date_range(?string $start, ?string $end): string
    {
        return month_year($start, '—') . ' — ' . month_year($end, 'Actual');
    }
}

if (! function_exists('duration_label')) {
    /**
     * Duración aproximada entre dos fechas: "1 a 8 m".
     */
    function duration_label(?string $start, ?string $end): string
    {
        if (empty($start)) {
            return '';
        }

        $from   = new DateTimeImmutable($start);
        $to     = new DateTimeImmutable($end ?: 'now');
        $diff   = $from->diff($to);
        $months = $diff->y * 12 + $diff->m + 1;

        $years     = intdiv($months, 12);
        $remaining = $months % 12;

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' a';
        }
        if ($remaining > 0) {
            $parts[] = $remaining . ' m';
        }

        return implode(' ', $parts);
    }
}

if (! function_exists('paragraphs')) {
    /**
     * Convierte texto plano con saltos de línea en párrafos escapados.
     */
    function paragraphs(?string $text, string $class = ''): string
    {
        if (empty(trim((string) $text))) {
            return '';
        }

        $attr   = $class !== '' ? ' class="' . e($class) . '"' : '';
        $blocks = preg_split('/\r\n\r\n|\n\n|\r\r/', trim($text)) ?: [];

        return implode('', array_map(
            static fn (string $block): string => '<p' . $attr . '>' . nl2br(e(trim($block))) . '</p>',
            $blocks
        ));
    }
}

if (! function_exists('excerpt')) {
    function excerpt(?string $text, int $limit = 160): string
    {
        $text = trim((string) $text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit), " \t\n\r\0\x0B.,;:") . '…';
    }
}
