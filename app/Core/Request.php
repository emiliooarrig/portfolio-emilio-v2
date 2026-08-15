<?php

namespace App\Core;

/**
 * Acceso normalizado a la petición HTTP entrante.
 */
class Request
{
    private static ?string $path = null;

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Ruta limpia, sin query string ni base_path, siempre con "/" inicial.
     */
    public static function path(): string
    {
        if (self::$path !== null) {
            return self::$path;
        }

        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(config('app.base_path', ''), '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . trim(rawurldecode($uri), '/');

        return self::$path = $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public static function input(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    public static function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }
}
