<?php

namespace App\Models;

use App\Core\Model;

/**
 * Mensajes del formulario de contacto.
 */
class ContactMessage extends Model
{
    /**
     * Valida y persiste un mensaje. Devuelve los errores por campo.
     *
     * @param  array<string, string> $input
     * @return array<string, string>
     */
    public function store(array $input, string $ip): array
    {
        $errors = $this->validate($input);

        if ($errors !== []) {
            return $errors;
        }

        $this->db()->execute(
            'INSERT INTO contact_messages (name, email, subject, message, ip_address)
             VALUES (?, ?, ?, ?, ?)',
            [
                mb_substr($input['name'], 0, 120),
                mb_substr($input['email'], 0, 150),
                mb_substr($input['subject'] ?? '', 0, 180),
                $input['message'],
                $ip,
            ]
        );

        return [];
    }

    /**
     * @param  array<string, string> $input
     * @return array<string, string>
     */
    private function validate(array $input): array
    {
        $errors = [];

        if (mb_strlen(trim($input['name'] ?? '')) < 2) {
            $errors['name'] = 'Escribe tu nombre.';
        }

        if (! filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Necesito un correo válido para responderte.';
        }

        if (mb_strlen(trim($input['message'] ?? '')) < 20) {
            $errors['message'] = 'Cuéntame un poco más — al menos 20 caracteres.';
        }

        return $errors;
    }
}
