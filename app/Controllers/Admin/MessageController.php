<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\ContactMessage;

/**
 * La bandeja del formulario de contacto.
 *
 * No hay alta ni edición: estos mensajes los escribe quien visita el sitio y
 * se guardan tal cual llegaron. Desde aquí sólo se leen, se marcan y se
 * tiran — retocar lo que alguien escribió sería inventarlo.
 */
class MessageController extends AdminController
{
    private const LIST = '/admin/mensajes';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/messages', [
            'messages' => (new ContactMessage())->adminList(),
        ], 'Mensajes');
    }

    /** El mensaje entero: la tabla lo recorta, aquí se lee completo. */
    public function show(string $id): void
    {
        Auth::requireLogin();

        $model   = new ContactMessage();
        $message = $model->find((int) $id);

        if ($message === null) {
            $this->missing(self::LIST, 'Ese mensaje');

            return;
        }

        // Abrirlo es leerlo: marcarlo a mano después sería pedir dos gestos
        // para una sola cosa.
        if ((int) $message['is_read'] === 0) {
            $model->setRead((int) $message['id'], true);
            $message['is_read'] = 1;
        }

        $this->render('admin/message', [
            'message' => $message,
            'back'    => self::LIST,
        ], 'Mensaje de ' . $message['name']);
    }

    /** Volver a marcarlo como nuevo, o darlo por leído desde el listado. */
    public function toggleRead(string $id): void
    {
        $messageId = (int) $id;

        $this->requireToken(self::LIST);

        $model   = new ContactMessage();
        $message = $model->find($messageId);

        if ($message === null) {
            $this->missing(self::LIST, 'Ese mensaje');

            return;
        }

        $read = (int) $message['is_read'] === 0;

        $model->setRead($messageId, $read);

        $this->flash('success', $read
            ? 'Mensaje de ' . $message['name'] . ' marcado como leído.'
            : 'Mensaje de ' . $message['name'] . ' marcado como nuevo.');

        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $message = (new ContactMessage())->find((int) $id);

        if ($message === null) {
            $this->missing(self::LIST, 'Ese mensaje');

            return;
        }

        $this->render('admin/confirm', [
            'title'  => 'Borrar el mensaje de ' . $message['name'],
            'lead'   => 'Es la única copia que hay. No hay deshacer.',
            'detail' => [
                'Correo'   => (string) $message['email'],
                'Asunto'   => (string) ($message['subject'] ?: '—'),
                'Recibido' => date('d/m/Y H:i', (int) strtotime((string) $message['created_at'])),
            ],
            'action' => self::LIST . '/' . (int) $message['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => 'Si sólo quieres quitarlo de en medio, márcalo como leído.',
        ], 'Borrar mensaje');
    }

    public function destroy(string $id): void
    {
        $messageId = (int) $id;

        $this->requireToken(self::LIST);

        $model   = new ContactMessage();
        $message = $model->find($messageId);

        if ($message === null) {
            $this->missing(self::LIST, 'Ese mensaje');

            return;
        }

        $model->delete($messageId);

        $this->flash('success', 'Mensaje de ' . $message['name'] . ' borrado.');
        $this->redirect(self::LIST);
    }
}
