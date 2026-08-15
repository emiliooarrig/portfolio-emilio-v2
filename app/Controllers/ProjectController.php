<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(): void
    {
        $this->render('projects/index', [
            'projects' => (new Project())->published(),
        ], 'Proyectos destacados', 'Plataformas de datos, pipelines y capas analíticas construidas de punta a punta.');
    }

    public function show(string $slug): void
    {
        $model   = new Project();
        $project = $model->findBySlug($slug);

        if ($project === null) {
            $this->notFound('Ese proyecto no existe o ya no está publicado.');

            return;
        }

        $this->render('projects/show', [
            'project'    => $project,
            'neighbours' => $model->neighbours((int) $project['sort_order'], (int) $project['id']),
        ], $project['title'], excerpt($project['summary'], 155));
    }
}
