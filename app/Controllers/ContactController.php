<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\ContactMessage;

/**
 * Formulario de contacto. No tiene vista propia: vive en la sección
 * #contacto de la landing y siempre regresa ahí.
 */
class ContactController extends Controller
{
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
            $this->backToContact();

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

        $this->backToContact();
    }

    private function backToContact(): void
    {
        header('Location: ' . url('/') . '#contacto');
        exit;
    }
}
