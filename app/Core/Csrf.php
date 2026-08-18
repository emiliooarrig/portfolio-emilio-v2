<?php

namespace App\Core;

/**
 * Token anti-CSRF, también guardado en la sesión.
 *
 * Sin él, otra página podría publicar un POST a /admin/login o, peor, a
 * /admin/logout con la cookie de sesión del navegador.
 */
final class Csrf
{
    private const KEY = 'csrf_token';

    /** Token de la sesión actual; se crea la primera vez que se pide. */
    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::KEY];
    }

    public static function check(string $token): bool
    {
        $stored = (string) ($_SESSION[self::KEY] ?? '');

        // hash_equals y no ===: la comparación no debe delatar por dónde falló.
        return $stored !== '' && $token !== '' && hash_equals($stored, $token);
    }

    /** Se quema el token: el siguiente formulario recibirá uno nuevo. */
    public static function rotate(): void
    {
        unset($_SESSION[self::KEY]);
    }
}
