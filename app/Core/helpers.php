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
     * <?= partial('section-header', ['title' => '…']) ?>
     *
     * @param array<string, mixed> $data
     */
    function partial(string $name, array $data = []): string
    {
        // El parcial se incluye dentro de una función anónima y no aquí
        // mismo por una razón concreta: `extract(EXTR_SKIP)` no pisa las
        // variables que ya existen, así que si esta función tuviera un
        // `$name` en su ámbito, un parcial que espera recibir `name` se
        // quedaría con el nombre del parcial. Dentro sólo viven `$__file`
        // y `$__data`, que ninguna vista usa.
        return (static function (string $__file, array $__data): string {
            if (! is_file($__file)) {
                return '';
            }

            extract($__data, EXTR_SKIP);

            ob_start();
            require $__file;

            return (string) ob_get_clean();
        })(APP_PATH . '/Views/partials/' . $name . '.php', $data);
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
     * Periodo legible: "mar 2025 – hoy". Lo que sigue abierto termina "hoy";
     * sin ninguna fecha no hay nada que decir.
     */
    function date_range(?string $start, ?string $end): string
    {
        if (empty($start)) {
            return empty($end) ? '' : month_year($end);
        }

        return month_year($start) . ' – ' . month_year($end, 'hoy');
    }
}

if (! function_exists('duration_label')) {
    /**
     * Duración aproximada entre dos fechas: "1 año y 8 meses".
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
            $parts[] = plural($years, 'año', 'años');
        }
        if ($remaining > 0) {
            $parts[] = plural($remaining, 'mes', 'meses');
        }

        return implode(' y ', $parts);
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

if (! function_exists('slugify')) {
    /**
     * "Migración a la nube" → "migracion-a-la-nube".
     *
     * Sin acentos, sin signos y sin espacios: es lo que acaba en la URL de
     * un proyecto. Se traduce a mano en vez de con iconv//TRANSLIT porque
     * ese depende de la configuración regional del servidor y en Windows
     * devuelve cosas distintas.
     */
    function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');

        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c', '&' => ' y ',
        ]);

        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

        return trim($text, '-');
    }
}

if (! function_exists('plural')) {
    /**
     * Concuerda un conteo con su palabra: "1 métrica" / "3 métricas".
     */
    function plural(int $count, string $singular, string $plural): string
    {
        return $count . ' ' . ($count === 1 ? $singular : $plural);
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
