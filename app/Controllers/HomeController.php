<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\HomeMetric;
use App\Models\Project;
use App\Models\Technology;

class HomeController extends Controller
{
    public function index(): void
    {
        $projects = new Project();

        $this->render('home/index', [
            'stack'           => (new Technology())->featured(10),
            'metrics'         => (new HomeMetric())->published(3),
            'pipeline'        => $projects->generalPipeline(),
            'featuredProjects'=> $projects->published(3),
            'currentRole'     => (new Experience())->current(),
            'certCount'       => (new Certification())->countPublished(),
            'projectCount'    => $projects->countPublished(),
        ], 'Inicio');
    }
}
