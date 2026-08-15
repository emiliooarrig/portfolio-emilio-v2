<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Certification;

class CertificationController extends Controller
{
    public function index(): void
    {
        $model = new Certification();

        $this->render('certifications/index', [
            'certifications' => $model->published(),
            'issuers'        => $model->issuers(),
        ], 'Certificaciones', 'Credenciales verificables en ingeniería de datos, nube y analítica.');
    }
}
