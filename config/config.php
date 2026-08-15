<?php
/**
 * Configuración de la aplicación.
 *
 * Para sobrescribir valores en local sin tocar este archivo, crea
 * `config/config.local.php` devolviendo un array con las mismas llaves.
 */

$config = [
    'app' => [
        'name'      => 'Emilio Guzmán · Ingeniería de Datos',
        'base_path' => '',              // subcarpeta si el sitio no vive en la raíz del dominio
        'debug'     => true,
        'timezone'  => 'America/Mexico_City',
        'locale'    => 'es_MX',
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'emiguzman',
        'user'    => 'root',
        'pass'    => 'root',
        'charset' => 'utf8mb4',
    ],
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $overrides = require $localConfig;
    if (is_array($overrides)) {
        foreach ($overrides as $section => $values) {
            $config[$section] = array_merge($config[$section] ?? [], (array) $values);
        }
    }
}

return $config;
