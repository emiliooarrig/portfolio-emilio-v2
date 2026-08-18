<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Project;

/**
 * Detalle de proyecto. No es una página: es el contenido del modal.
 *
 * - Petición AJAX  → devuelve sólo el fragmento HTML que se inyecta.
 * - Petición directa → devuelve la landing completa con el modal ya abierto,
 *   para que /proyectos/{slug} siga siendo un enlace compartible.
 */
class ProjectController extends Controller
{
    public function detail(string $slug): void
    {
        $project = (new Project())->findBySlug($slug);

        if ($project === null) {
            if (Request::isAjax()) {
                http_response_code(404);
                echo '<p class="empty-state">Ese proyecto no existe o ya no está publicado.</p>';

                return;
            }

            $this->notFound('Ese proyecto no existe o ya no está publicado.');

            return;
        }

        if (Request::isAjax()) {
            echo partial('project-detail', ['project' => $project]);

            return;
        }

        (new HomeController())->index($project);
    }
}
