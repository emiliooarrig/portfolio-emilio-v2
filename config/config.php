<?php
/**
 * Configuración de la aplicación.
 *
 * Los valores sensibles (credenciales de la base de datos) viven en el
 * archivo `.env` de la raíz, fuera del repositorio. Copia `.env.example`
 * como `.env` y ajústalo. Lo que queda aquí son los valores por defecto
 * para un entorno local, que se usan si la variable no está definida.
 */

require_once dirname(__DIR__) . '/app/Core/Env.php';

use App\Core\Env;

Env::load(dirname(__DIR__) . '/.env');

return [
    'app' => [
        'name'      => Env::get('APP_NAME', 'Emilio Guzmán · Ingeniería de Datos'),
        'base_path' => Env::get('APP_BASE_PATH', ''),   // subcarpeta si el sitio no vive en la raíz del dominio
        'debug'     => (bool) Env::get('APP_DEBUG', true),
        'timezone'  => Env::get('APP_TIMEZONE', 'America/Mexico_City'),
        'locale'    => Env::get('APP_LOCALE', 'es_MX'),
    ],

    'db' => [
        'host'    => Env::get('DB_HOST', '127.0.0.1'),
        'port'    => (int) Env::get('DB_PORT', 3306),
        'name'    => Env::get('DB_NAME', 'emiguzman'),
        'user'    => Env::get('DB_USER', 'root'),
        'pass'    => (string) Env::get('DB_PASS', ''),
        'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    ],
];
