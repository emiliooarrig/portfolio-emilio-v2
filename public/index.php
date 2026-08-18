<?php

/**
 * Front controller — único punto de entrada de la aplicación.
 */

declare(strict_types=1);

define('BASE_PATH',   dirname(__DIR__));
define('APP_PATH',    BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

require APP_PATH . '/Core/helpers.php';

// Autoload PSR-4 sencillo: App\Foo\Bar → app/Foo/Bar.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = APP_PATH . '/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

date_default_timezone_set((string) config('app.timezone', 'UTC'));

if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// La sesión sostiene el acceso al panel, así que su cookie se cierra antes
// de abrirla: sin acceso desde JavaScript, sin viajar a sitios ajenos y, en
// cuanto haya HTTPS, sólo por HTTPS.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off',
]);

session_start();

App\Core\Database::instance(config('db'));

$router = new App\Core\Router(require CONFIG_PATH . '/routes.php');

try {
    $router->dispatch(App\Core\Request::method(), App\Core\Request::path());
} catch (Throwable $e) {
    http_response_code(500);

    if (config('app.debug')) {
        throw $e;
    }

    echo '<h1>500</h1><p>Algo falló del lado del servidor.</p>';
}
