<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Experience;
use App\Models\Technology;

class AboutController extends Controller
{
    public function index(): void
    {
        $this->render('about/index', [
            'stackByCategory' => (new Technology())->groupedByCategory(),
            'careerStart'     => (new Experience())->careerStartYear(),
            'currentRole'     => (new Experience())->current(),
        ], 'Sobre mí', 'Perfil profesional: ingeniería de TI aplicada a plataformas de datos.');
    }
}
