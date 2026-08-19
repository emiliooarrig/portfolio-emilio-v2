<?php

namespace App\Core;

/**
 * Lector mínimo de archivos `.env`.
 *
 * No hay Composer en el proyecto, así que en lugar de una dependencia se
 * hace lo justo: leer el archivo una vez, guardar los pares en memoria y
 * resolverlos con `Env::get()`. Las variables reales del entorno (las que
 * define el hosting o Apache) mandan sobre el archivo: en producción se
 * configuran ahí y el `.env` ni siquiera existe.
 */
final class Env
{
    /** @var array<string, string>|null */
    private static ?array $vars = null;

    /**
     * Carga el archivo indicado. Sólo la primera llamada tiene efecto.
     */
    public static function load(string $path): void
    {
        if (self::$vars !== null) {
            return;
        }

        self::$vars = [];

        if (! is_file($path) || ! is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim(preg_replace('/^export\s+/', '', trim($key)) ?? '');

            if ($key === '') {
                continue;
            }

            self::$vars[$key] = self::clean(trim($value));
        }
    }

    /**
     * Valor de una variable, ya convertido a bool / int / null cuando aplica.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            $fromSystem = getenv($key);
            $value      = $fromSystem === false ? (self::$vars[$key] ?? null) : $fromSystem;
        }

        if ($value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            ''                 => '',
            default            => is_numeric($value) && ! str_contains((string) $value, '.')
                ? (int) $value
                : (string) $value,
        };
    }

    /**
     * Quita comillas envolventes y el comentario final de un valor sin comillas.
     */
    private static function clean(string $value): string
    {
        if (strlen($value) > 1) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];

            if (($first === '"' || $first === "'") && $first === $last) {
                $value = substr($value, 1, -1);

                return $first === '"'
                    ? str_replace(['\n', '\r', '\\"'], ["\n", "\r", '"'], $value)
                    : $value;
            }
        }

        // Sin comillas, un `#` al inicio o precedido de espacio abre un
        // comentario; pegado al texto (`p#ss`) forma parte del valor.
        $value = preg_replace('/(^|\s+)#.*$/', '', $value) ?? $value;

        return trim($value);
    }
}
