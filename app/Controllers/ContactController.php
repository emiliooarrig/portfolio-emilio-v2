<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('contact/index', [
            'errors' => $_SESSION['contact_errors'] ?? [],
            'old'    => $_SESSION['contact_old'] ?? [],
            'flash'  => $flash,
        ], 'Contacto', 'Escríbeme para proyectos de datos, consultoría o colaboración.');

        unset($_SESSION['contact_errors'], $_SESSION['contact_old']);
    }

    public function store(): void
    {
        $input = [
            'name'    => Request::input('name'),
            'email'   => Request::input('email'),
            'subject' => Request::input('subject'),
            'message' => Request::input('message'),
        ];

        // Honeypot: los bots llenan campos ocultos; se acepta en silencio y se descarta.
        if (Request::input('website') !== '') {
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Mensaje enviado. Te respondo pronto.'];
            $this->redirect('/contacto');

            return;
        }

        $errors = (new ContactMessage())->store($input, Request::ip());

        if ($errors !== []) {
            $_SESSION['contact_errors'] = $errors;
            $_SESSION['contact_old']    = $input;
            $_SESSION['flash']          = ['type' => 'error', 'text' => 'Revisa los campos marcados.'];
        } else {
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Mensaje recibido. Te respondo en menos de 24 horas.'];
        }

        $this->redirect('/contacto');
    }
}
