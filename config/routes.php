<?php
/**
 * Tabla de rutas.
 *
 * El sitio es una landing de una sola página: todo el contenido vive en `/`
 * y se navega por anclas. Las únicas rutas extra son el detalle de proyecto
 * (fragmento del modal, o la landing con el modal abierto si se entra directo)
 * y el envío del formulario de contacto.
 *
 * Aparte vive el panel de administración (`/admin`), que no es parte de la
 * landing: no está en el nav, no se indexa y todo lo suyo exige sesión. Ahí
 * sí hay rutas por pantalla — es una herramienta, no un scroll.
 *
 * Cada sección editable del panel repite el mismo juego:
 *
 *   GET  /admin/x                 listado
 *   GET  /admin/x/nuevo           formulario en blanco
 *   POST /admin/x                 alta
 *   GET  /admin/x/{id}/editar     formulario con la fila
 *   POST /admin/x/{id}            guardado
 *   GET  /admin/x/{id}/eliminar   confirmación
 *   POST /admin/x/{id}/eliminar   borrado
 *
 * El borrado se confirma en su propia pantalla y no con un diálogo del
 * navegador: el panel va sin JavaScript, y un botón que borra al primer clic
 * no tiene vuelta atrás.
 *
 * Formato: 'METODO ruta' => 'Controlador@accion'
 * Los segmentos dinámicos se declaran con llaves: {slug}
 */

return [
    'GET /'                 => 'HomeController@index',
    'GET /proyectos/{slug}' => 'ProjectController@detail',
    'POST /contacto'        => 'ContactController@store',

    // Acceso. No hay ruta de registro: las cuentas se crean desde dentro.
    'GET /admin/login'   => 'AuthController@showLogin',
    'POST /admin/login'  => 'AuthController@login',
    'POST /admin/logout' => 'AuthController@logout',

    'GET /admin' => 'Admin\DashboardController@index',

    // Proyectos
    'GET /admin/proyectos'                => 'Admin\ProjectController@index',
    'GET /admin/proyectos/nuevo'          => 'Admin\ProjectController@create',
    'POST /admin/proyectos'               => 'Admin\ProjectController@store',
    'GET /admin/proyectos/{id}/editar'    => 'Admin\ProjectController@edit',
    'POST /admin/proyectos/{id}'          => 'Admin\ProjectController@update',
    'GET /admin/proyectos/{id}/eliminar'  => 'Admin\ProjectController@confirmDelete',
    'POST /admin/proyectos/{id}/eliminar' => 'Admin\ProjectController@destroy',

    // Servicios
    'GET /admin/servicios'                => 'Admin\ServiceController@index',
    'GET /admin/servicios/nuevo'          => 'Admin\ServiceController@create',
    'POST /admin/servicios'               => 'Admin\ServiceController@store',
    'GET /admin/servicios/{id}/editar'    => 'Admin\ServiceController@edit',
    'POST /admin/servicios/{id}'          => 'Admin\ServiceController@update',
    'GET /admin/servicios/{id}/eliminar'  => 'Admin\ServiceController@confirmDelete',
    'POST /admin/servicios/{id}/eliminar' => 'Admin\ServiceController@destroy',

    // Certificaciones
    'GET /admin/certificaciones'                => 'Admin\CertificationController@index',
    'GET /admin/certificaciones/nueva'          => 'Admin\CertificationController@create',
    'POST /admin/certificaciones'               => 'Admin\CertificationController@store',
    'GET /admin/certificaciones/{id}/editar'    => 'Admin\CertificationController@edit',
    'POST /admin/certificaciones/{id}'          => 'Admin\CertificationController@update',
    'GET /admin/certificaciones/{id}/eliminar'  => 'Admin\CertificationController@confirmDelete',
    'POST /admin/certificaciones/{id}/eliminar' => 'Admin\CertificationController@destroy',

    // Experiencia
    'GET /admin/experiencia'                => 'Admin\ExperienceController@index',
    'GET /admin/experiencia/nuevo'          => 'Admin\ExperienceController@create',
    'POST /admin/experiencia'               => 'Admin\ExperienceController@store',
    'GET /admin/experiencia/{id}/editar'    => 'Admin\ExperienceController@edit',
    'POST /admin/experiencia/{id}'          => 'Admin\ExperienceController@update',
    'GET /admin/experiencia/{id}/eliminar'  => 'Admin\ExperienceController@confirmDelete',
    'POST /admin/experiencia/{id}/eliminar' => 'Admin\ExperienceController@destroy',

    // Stack
    'GET /admin/tecnologias'                => 'Admin\TechnologyController@index',
    'GET /admin/tecnologias/nueva'          => 'Admin\TechnologyController@create',
    'POST /admin/tecnologias'               => 'Admin\TechnologyController@store',
    'GET /admin/tecnologias/{id}/editar'    => 'Admin\TechnologyController@edit',
    'POST /admin/tecnologias/{id}'          => 'Admin\TechnologyController@update',
    'GET /admin/tecnologias/{id}/eliminar'  => 'Admin\TechnologyController@confirmDelete',
    'POST /admin/tecnologias/{id}/eliminar' => 'Admin\TechnologyController@destroy',

    // Perfil: fila única, así que sólo lectura y edición.
    'GET /admin/perfil'        => 'Admin\ProfileController@index',
    'GET /admin/perfil/editar' => 'Admin\ProfileController@edit',
    'POST /admin/perfil'       => 'Admin\ProfileController@update',

    // Mensajes: los escribe quien visita el sitio, así que aquí no se crean
    // ni se editan. Se leen, se marcan y se borran.
    'GET /admin/mensajes'                => 'Admin\MessageController@index',
    'GET /admin/mensajes/{id}'           => 'Admin\MessageController@show',
    'POST /admin/mensajes/{id}/leido'    => 'Admin\MessageController@toggleRead',
    'GET /admin/mensajes/{id}/eliminar'  => 'Admin\MessageController@confirmDelete',
    'POST /admin/mensajes/{id}/eliminar' => 'Admin\MessageController@destroy',
];
