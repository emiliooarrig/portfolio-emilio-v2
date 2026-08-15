<?php

namespace App\Models;

use App\Core\Model;

/**
 * Datos globales del sitio (fila única de `profile`).
 */
class Profile extends Model
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $row = $this->one('SELECT * FROM profile WHERE id = 1 LIMIT 1');

        return self::$cache = $row ?? $this->fallback();
    }

    /**
     * Valores mínimos para que el sitio no se rompa si la tabla está vacía.
     *
     * @return array<string, mixed>
     */
    private function fallback(): array
    {
        return [
            'full_name'          => 'Emilio Guzmán',
            'role_title'         => 'Ingeniero de TI · Ingeniería de Datos',
            'headline'           => '',
            'bio_short'          => '',
            'bio_long'           => '',
            'email'              => '',
            'phone'              => null,
            'location'           => null,
            'avatar_path'        => null,
            'cv_path'            => null,
            'github_url'         => null,
            'linkedin_url'       => null,
            'website_url'        => null,
            'years_experience'   => 0,
            'available_for_work' => 0,
        ];
    }
}
