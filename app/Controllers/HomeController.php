<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;

/**
 * Única vista del sitio: arma los datos de todas las secciones del scroll.
 */
class HomeController extends Controller
{
    /**
     * @param array<string, mixed>|null $openProject
     *        Proyecto que debe llegar ya abierto en el modal (entrada directa
     *        a /proyectos/{slug} sin JS o con la página aún sin cargar).
     */
    public function index(?array $openProject = null): void
    {
        $projects       = new Project();
        $experiences    = new Experience();
        $certifications = new Certification();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('home/index', [
            'landing'         => true,
            'projects'        => $projects->published(),
            'projectCount'    => $projects->countPublished(),
            'services'        => (new Service())->published(),
            'stackByCategory' => (new Technology())->groupedByCategory(),
            'experiences'     => $experiences->published(),
            'currentRole'     => $experiences->current(),
            'careerStart'     => $experiences->careerStartYear(),
            'certifications'  => $certifications->published(),
            'issuers'         => $certifications->issuers(),
            'openProject'     => $openProject,
            'contactErrors'   => $_SESSION['contact_errors'] ?? [],
            'contactOld'      => $_SESSION['contact_old'] ?? [],
            'flash'           => $flash,
        ]);

        unset($_SESSION['contact_errors'], $_SESSION['contact_old']);
    }
}
