<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;

/**
 * Entrada y salida del panel. No hay registro: las cuentas se crean desde
 * dentro, así que esta es la única puerta.
 */
class AuthController extends Controller
{
    protected string $layout = 'auth';

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin');

            return;
        }

        // El mensaje viaja en sesión desde el POST anterior (patrón
        // POST-redirect-GET: recargar no reenvía la contraseña).
        $error  = $_SESSION['admin_login_error'] ?? null;
        $notice = $_SESSION['admin_login_notice'] ?? null;
        $old    = $_SESSION['admin_login_old'] ?? '';

        unset($_SESSION['admin_login_error'], $_SESSION['admin_login_notice'], $_SESSION['admin_login_old']);

        $this->render('admin/login', [
            'error'   => $error,
            'notice'  => $notice,
            'oldUser' => $old,
            'token'   => Csrf::token(),
        ], 'Acceso al panel');
    }

    public function login(): void
    {
        $username = Request::input('username');
        // Sin trim: un espacio al principio o al final puede ser parte de la
        // contraseña, y recortarlo cambiaría en silencio lo que se tecleó.
        $password = (string) ($_POST['password'] ?? '');

        if (! Csrf::check(Request::input('_token'))) {
            $this->fail('La sesión del formulario caducó. Vuelve a intentarlo.', $username);

            return;
        }

        $wait = Auth::lockedFor();

        if ($wait > 0) {
            $this->fail('Demasiados intentos. Espera ' . $wait . ' segundos.', $username);

            return;
        }

        if ($username === '' || $password === '') {
            $this->fail('Escribe tu usuario y tu contraseña.', $username);

            return;
        }

        if (! Auth::attempt($username, $password)) {
            // Un solo mensaje para los dos casos: decir cuál falló es decirle
            // a quien prueba que ese usuario existe.
            $this->fail('Usuario o contraseña incorrectos.', $username);

            return;
        }

        Csrf::rotate();
        $this->redirect('/admin');
    }

    public function logout(): void
    {
        if (Csrf::check(Request::input('_token'))) {
            Auth::logout();
            Csrf::rotate();
            $_SESSION['admin_login_notice'] = 'Sesión cerrada.';
        }

        $this->redirect('/admin/login');
    }

    private function fail(string $message, string $username): void
    {
        $_SESSION['admin_login_error'] = $message;
        $_SESSION['admin_login_old']   = $username;

        $this->redirect('/admin/login');
    }
}
