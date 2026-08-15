<?php
/**
 * Tabla de rutas.
 *
 * Formato: 'METODO ruta' => 'Controlador@accion'
 * Los segmentos dinámicos se declaran con llaves: {slug}
 */

return [
    'GET /'                    => 'HomeController@index',
    'GET /sobre-mi'            => 'AboutController@index',
    'GET /proyectos'           => 'ProjectController@index',
    'GET /proyectos/{slug}'    => 'ProjectController@show',
    'GET /certificaciones'     => 'CertificationController@index',
    'GET /experiencia'         => 'ExperienceController@index',
    'GET /contacto'            => 'ContactController@index',
    'POST /contacto'           => 'ContactController@store',
];
