<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;

/**
 * Resumen del panel: cuánto hay de cada cosa y lo último que llegó.
 *
 * Es la pantalla de aterrizaje tras el login, así que cada tarjeta lleva a
 * su sección: desde aquí no se lee nada a fondo, se decide a dónde ir.
 */
class DashboardController extends AdminController
{
    public function index(): void
    {
        Auth::requireLogin();

        $projects       = (new Project())->adminList();
        $services       = (new Service())->adminList();
        $certifications = (new Certification())->adminList();
        $experiences    = (new Experience())->adminList();
        $technologies   = (new Technology())->adminList();

        // Se traen enteros y se cuentan en PHP: son decenas de filas, no
        // miles, y así el resumen sale de las mismas consultas que ya usan
        // las tablas en vez de duplicarlas en COUNT(*) sueltos.
        $messages = (new ContactMessage())->adminList();

        $unread = count(array_filter(
            $messages,
            static fn (array $m): bool => (int) $m['is_read'] === 0
        ));

        $this->render('admin/dashboard', [
            'summary' => [
                $this->card('Proyectos', '/admin/proyectos', $projects),
                $this->card('Servicios', '/admin/servicios', $services),
                $this->card('Certificaciones', '/admin/certificaciones', $certifications),
                $this->card('Experiencia', '/admin/experiencia', $experiences),
                [
                    'label'  => 'Tecnologías',
                    'path'   => '/admin/tecnologias',
                    'total'  => count($technologies),
                    // El stack no tiene publicado/oculto: se muestra completo.
                    'detail' => 'en el stack',
                ],
                [
                    'label'  => 'Mensajes',
                    'path'   => '/admin/mensajes',
                    'total'  => count($messages),
                    'detail' => $unread === 0 ? 'todos leídos' : $unread . ' sin leer',
                ],
            ],
            'recent' => array_slice($messages, 0, 5),
            'unread' => $unread,
        ], 'Resumen');
    }

    /**
     * Tarjeta del resumen para una tabla con bandera `is_published`.
     *
     * @param  array<int, array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    private function card(string $label, string $path, array $rows): array
    {
        $published = count(array_filter(
            $rows,
            static fn (array $row): bool => (int) ($row['is_published'] ?? 0) === 1
        ));

        $hidden = count($rows) - $published;

        return [
            'label'  => $label,
            'path'   => $path,
            'total'  => count($rows),
            'detail' => $hidden === 0
                ? 'todo publicado'
                : $published . ' visibles · ' . $hidden . ' ocultos',
        ];
    }
}
