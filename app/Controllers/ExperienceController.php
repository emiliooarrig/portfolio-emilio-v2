<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Experience;

class ExperienceController extends Controller
{
    public function index(): void
    {
        $model = new Experience();

        $this->render('experience/index', [
            'experiences' => $model->published(),
            'careerStart' => $model->careerStartYear(),
        ], 'Experiencia laboral', 'Trayectoria profesional en infraestructura, sistemas e ingeniería de datos.');
    }
}
