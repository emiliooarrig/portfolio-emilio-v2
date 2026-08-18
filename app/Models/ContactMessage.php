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

    // --------------------------------------------------------
    //  Panel de administración — la bandeja
    // --------------------------------------------------------

    /**
     * Lo más reciente primero: un mensaje sin leer de ayer pesa más que uno
     * de hace un mes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminList(?int $limit = null): array
    {
        $sql = 'SELECT id, name, email, subject, message, ip_address, is_read, created_at
                  FROM contact_messages
              ORDER BY created_at DESC, id DESC';

        $params = [];

        if ($limit !== null) {
            $sql     .= ' LIMIT ?';
            $params[] = $limit;
        }

        return $this->all($sql, $params);
    }

    public function unreadCount(): int
    {
        $row = $this->one('SELECT COUNT(*) AS total FROM contact_messages WHERE is_read = 0');

        return (int) ($row['total'] ?? 0);
    }

    // --------------------------------------------------------
    //  Panel: la bandeja
    //
    //  Aquí no hay alta ni edición a propósito: los mensajes los
    //  escribe quien visita el sitio. Desde dentro sólo se leen,
    //  se marcan y se tiran.
    // --------------------------------------------------------

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM contact_messages WHERE id = ? LIMIT 1', [$id]);
    }

    public function setRead(int $id, bool $read): void
    {
        $this->db()->execute(
            'UPDATE contact_messages SET is_read = ? WHERE id = ?',
            [$read ? 1 : 0, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM contact_messages WHERE id = ?', [$id]);
    }
}
