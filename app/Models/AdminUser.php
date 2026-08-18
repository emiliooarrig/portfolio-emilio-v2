<?php

namespace App\Models;

use App\Core\Model;

/**
 * Usuarios del panel. No hay alta pública: las cuentas se crean desde el
 * propio panel, así que aquí sólo se consulta y se marca el último acceso.
 */
class AdminUser extends Model
{
    /**
     * Busca por nombre de usuario (no por correo) y sólo si está activo.
     *
     * @return array<string, mixed>|null
     */
    public function findActiveByUsername(string $username): ?array
    {
        return $this->one(
            'SELECT id, username, display_name, password_hash
               FROM admin_users
              WHERE username = ? AND is_active = 1',
            [$username]
        );
    }

    public function touchLastLogin(int $id): void
    {
        $this->db()->execute('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }
}
