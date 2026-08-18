<?php

namespace App\Core;

use App\Models\AdminUser;

/**
 * Sesión del panel de administración.
 *
 * Todo el estado vive en `$_SESSION`: no hay tokens propios, ni cookies
 * aparte de la de sesión de PHP, ni "recordarme". El front controller ya
 * llamó a `session_start()`; aquí sólo se lee y se escribe.
 *
 * Se guarda bajo una única llave (`$_SESSION['admin']`) para que cerrar
 * sesión no arrastre lo que la landing pública tenga guardado — el flash
 * del formulario de contacto, por ejemplo.
 */
final class Auth
{
    /** Llave única dentro de $_SESSION. */
    private const KEY = 'admin';

    /** Llave del contador de intentos fallidos. */
    private const ATTEMPTS_KEY = 'admin_login_attempts';

    /** Sesión inactiva que se cierra sola (segundos). */
    private const IDLE_TIMEOUT = 7200; // 2 h

    /** Intentos fallidos seguidos antes de bloquear, y cuánto dura el bloqueo. */
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT      = 60;

    /**
     * Hash de descarte para cuando el usuario no existe. Comparar siempre
     * cuesta lo mismo: si sólo se verificara con usuarios reales, el tiempo
     * de respuesta diría cuáles existen.
     */
    private const DUMMY_HASH = '$2y$12$E/CSOq1M1j9pVaz2sitp3eFTlSfJ7KMH8yrM6WzChJwPVoObeXTMO';

    /**
     * Valida credenciales y, si son correctas, abre la sesión.
     */
    public static function attempt(string $username, string $password): bool
    {
        $user  = (new AdminUser())->findActiveByUsername($username);
        $hash  = is_array($user) ? (string) $user['password_hash'] : self::DUMMY_HASH;
        $valid = password_verify($password, $hash);

        if (! $valid || ! is_array($user)) {
            self::registerFailure();

            return false;
        }

        self::start($user);

        return true;
    }

    /**
     * ¿Hay sesión abierta? De paso caduca las que llevan demasiado tiempo
     * quietas: un panel abierto y olvidado no debe seguir sirviendo.
     */
    public static function check(): bool
    {
        $session = $_SESSION[self::KEY] ?? null;

        if (! is_array($session)) {
            return false;
        }

        if (time() - (int) ($session['seen_at'] ?? 0) > self::IDLE_TIMEOUT) {
            self::logout();

            return false;
        }

        $_SESSION[self::KEY]['seen_at'] = time();

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return self::check() ? $_SESSION[self::KEY] : null;
    }

    /**
     * Corta la petición y manda al login. Lo llama toda pantalla del panel
     * antes de imprimir nada.
     */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        $_SESSION['admin_login_notice'] = 'Inicia sesión para entrar al panel.';

        header('Location: ' . url('/admin/login'));
        exit;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::KEY]);

        // El identificador con el que se estuvo dentro no sigue vivo fuera.
        session_regenerate_id(true);
    }

    /**
     * Segundos que faltan para poder volver a intentarlo (0 = no hay bloqueo).
     */
    public static function lockedFor(): int
    {
        $until = (int) ($_SESSION[self::ATTEMPTS_KEY]['until'] ?? 0);

        return max(0, $until - time());
    }

    /**
     * @param array<string, mixed> $user
     */
    private static function start(array $user): void
    {
        // Contra fijación de sesión: el id con el que se llegó al formulario
        // no sirve para quedarse dentro.
        session_regenerate_id(true);

        $_SESSION[self::KEY] = [
            'id'           => (int) $user['id'],
            'username'     => (string) $user['username'],
            'display_name' => (string) $user['display_name'],
            'login_at'     => time(),
            'seen_at'      => time(),
        ];

        unset($_SESSION[self::ATTEMPTS_KEY]);

        (new AdminUser())->touchLastLogin((int) $user['id']);
    }

    private static function registerFailure(): void
    {
        $count = (int) ($_SESSION[self::ATTEMPTS_KEY]['count'] ?? 0) + 1;

        if ($count >= self::MAX_ATTEMPTS) {
            // El bloqueo reinicia la cuenta: al vencer se vuelve a empezar.
            $_SESSION[self::ATTEMPTS_KEY] = ['count' => 0, 'until' => time() + self::LOCKOUT];

            return;
        }

        $_SESSION[self::ATTEMPTS_KEY] = ['count' => $count, 'until' => 0];
    }
}
