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

    // --------------------------------------------------------
    //  Panel: escritura
    //
    //  Fila única: no hay alta ni baja, sólo edición. Si la tabla
    //  estuviera vacía, el UPDATE no encontraría nada y el sitio
    //  seguiría con los valores de respaldo — por eso se inserta la
    //  fila 1 la primera vez.
    // --------------------------------------------------------

    /**
     * @param  array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input): array
    {
        $errors = [];

        $required = [
            'full_name'  => ['Falta tu nombre.', 120],
            'role_title' => ['Falta el rol con el que te presentas.', 160],
            'headline'   => ['Falta el titular: la frase que resume a qué te dedicas.', 255],
        ];

        foreach ($required as $field => [$message, $max]) {
            $value = trim((string) ($input[$field] ?? ''));

            if ($value === '') {
                $errors[$field] = $message;
            } elseif (mb_strlen($value) > $max) {
                $errors[$field] = 'Máximo ' . $max . ' caracteres.';
            }
        }

        $email = trim((string) ($input['email'] ?? ''));

        if ($email === '') {
            $errors['email'] = 'El correo de contacto es obligatorio: es por donde te escriben.';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ese correo no es válido.';
        }

        foreach (['github_url', 'linkedin_url', 'website_url'] as $field) {
            $url = trim((string) ($input[$field] ?? ''));

            if ($url !== '' && ! $this->isUrl($url)) {
                $errors[$field] = 'La dirección debe empezar por http:// o https://.';
            }
        }

        $years = (int) ($input['years_experience'] ?? 0);

        if ($years < 0 || $years > 60) {
            $errors['years_experience'] = 'Escribe un número entre 0 y 60.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(array $input): void
    {
        $columns = [
            $this->fit((string) ($input['full_name'] ?? ''), 120),
            $this->fit((string) ($input['role_title'] ?? ''), 160),
            $this->fit((string) ($input['headline'] ?? ''), 255),
            $this->nullify((string) ($input['bio_short'] ?? '')),
            $this->nullify((string) ($input['bio_long'] ?? '')),
            $this->fit((string) ($input['email'] ?? ''), 150),
            $this->fit((string) ($input['phone'] ?? ''), 40),
            $this->fit((string) ($input['location'] ?? ''), 120),
            $this->fit((string) ($input['avatar_path'] ?? ''), 255),
            $this->fit((string) ($input['cv_path'] ?? ''), 255),
            $this->fit((string) ($input['github_url'] ?? ''), 255),
            $this->fit((string) ($input['linkedin_url'] ?? ''), 255),
            $this->fit((string) ($input['website_url'] ?? ''), 255),
            (int) ($input['years_experience'] ?? 0),
            (int) ($input['available_for_work'] ?? 0),
        ];

        // Un solo INSERT con actualización: sirve igual para la primera vez
        // que para las siguientes, sin preguntar antes si la fila existe.
        $this->db()->execute(
            'INSERT INTO profile
                (`id`, `full_name`, `role_title`, `headline`, `bio_short`, `bio_long`, `email`,
                 `phone`, `location`, `avatar_path`, `cv_path`, `github_url`, `linkedin_url`,
                 `website_url`, `years_experience`, `available_for_work`)
             VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                `full_name` = VALUES(`full_name`), `role_title` = VALUES(`role_title`),
                `headline` = VALUES(`headline`), `bio_short` = VALUES(`bio_short`),
                `bio_long` = VALUES(`bio_long`), `email` = VALUES(`email`),
                `phone` = VALUES(`phone`), `location` = VALUES(`location`),
                `avatar_path` = VALUES(`avatar_path`), `cv_path` = VALUES(`cv_path`),
                `github_url` = VALUES(`github_url`), `linkedin_url` = VALUES(`linkedin_url`),
                `website_url` = VALUES(`website_url`),
                `years_experience` = VALUES(`years_experience`),
                `available_for_work` = VALUES(`available_for_work`)',
            $columns
        );

        // La caché estática guardaba la fila anterior: quien lea el perfil
        // después de guardar debe ver lo nuevo, no lo de hace un instante.
        self::$cache = null;
    }
}
