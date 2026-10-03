<?php

namespace App\Models;

use App\Core\Model;

/**
 * Servicios que ofrezco: alimenta el carrusel de la landing.
 *
 * El texto viene ya escrito para un cliente sin conocimientos técnicos;
 * este modelo sólo lo entrega tal cual, con las viñetas ya decodificadas.
 */
class Service extends Model
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function published(): array
    {
        $rows = $this->all(
            'SELECT id, slug, title, tagline, description, deliverables,
                    outcome, timeframe, icon, is_featured
               FROM services
              WHERE is_published = 1
           ORDER BY id ASC'
        );

        return array_map([$this, 'withDeliverables'], $rows);
    }

    public function countPublished(): int
    {
        $row = $this->one('SELECT COUNT(*) AS total FROM services WHERE is_published = 1');

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Convierte la columna JSON en un array de cadenas. Un JSON inválido o
     * vacío deja la tarjeta sin viñetas, nunca rompe la página.
     *
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function withDeliverables(array $row): array
    {
        $decoded = json_decode((string) ($row['deliverables'] ?? ''), true);

        $row['deliverables'] = is_array($decoded)
            ? array_values(array_filter(array_map('strval', $decoded)))
            : [];

        return $row;
    }

    // --------------------------------------------------------
    //  Panel de administración — publicados y ocultos
    // --------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    public function adminList(): array
    {
        $rows = $this->all(
            'SELECT id, slug, title, tagline, description, deliverables, outcome,
                    timeframe, icon, is_featured, is_published, updated_at
               FROM services
           ORDER BY id ASC'
        );

        return array_map([$this, 'withDeliverables'], $rows);
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /** Claves de icono que la tarjeta de servicio sabía dibujar (la sección salió de la landing). */
    public const ICONS = [
        'spark'   => 'Chispa',
        'browser' => 'Navegador',
        'layers'  => 'Capas',
        'bolt'    => 'Rayo',
        'boxes'   => 'Bloques',
        'chart'   => 'Gráfica',
        'shield'  => 'Escudo',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->one('SELECT * FROM services WHERE id = ? LIMIT 1', [$id]);

        return $row === null ? null : $this->withDeliverables($row);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input, ?int $id = null): array
    {
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));

        if ($title === '') {
            $errors['title'] = 'El servicio necesita un nombre.';
        } elseif (mb_strlen($title) > 120) {
            $errors['title'] = 'Máximo 120 caracteres.';
        }

        $slug = trim((string) ($input['slug'] ?? ''));

        if ($slug === '') {
            $slug = slugify($title);
        }

        if ($slug === '') {
            $errors['slug'] = 'No sale una clave del nombre: escríbela a mano.';
        } elseif (! $this->isSlug($slug)) {
            $errors['slug'] = 'Sólo minúsculas, números y guiones.';
        } elseif (mb_strlen($slug) > 80) {
            $errors['slug'] = 'Máximo 80 caracteres.';
        } elseif ($this->slugTaken('services', $slug, $id)) {
            $errors['slug'] = 'Ya hay otro servicio con esa clave.';
        }

        $description = trim((string) ($input['description'] ?? ''));

        if ($description === '') {
            $errors['description'] = 'Explica qué haces, en palabras del cliente.';
        } elseif (mb_strlen($description) > 500) {
            $errors['description'] = 'Máximo 500 caracteres.';
        }

        foreach (['tagline' => 160, 'outcome' => 200, 'timeframe' => 60] as $field => $max) {
            if (mb_strlen(trim((string) ($input[$field] ?? ''))) > $max) {
                $errors[$field] = 'Máximo ' . $max . ' caracteres.';
            }
        }

        if (! array_key_exists((string) ($input['icon'] ?? ''), self::ICONS)) {
            $errors['icon'] = 'Elige uno de los iconos disponibles.';
        }

        foreach ((array) ($input['deliverables'] ?? []) as $item) {
            if (mb_strlen((string) $item) > 120) {
                $errors['deliverables'] = 'Cada viñeta va corta: máximo 120 caracteres.';
                break;
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function create(array $input): int
    {
        return $this->db()->execute(
            'INSERT INTO services
                (`slug`, `title`, `tagline`, `description`, `deliverables`, `outcome`,
                 `timeframe`, `icon`, `is_featured`, `is_published`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $this->columns($input)
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(int $id, array $input): void
    {
        $params   = $this->columns($input);
        $params[] = $id;

        $this->db()->execute(
            'UPDATE services SET
                `slug` = ?, `title` = ?, `tagline` = ?, `description` = ?, `deliverables` = ?,
                `outcome` = ?, `timeframe` = ?, `icon` = ?, `is_featured` = ?,
                `is_published` = ?
              WHERE id = ?',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM services WHERE id = ?', [$id]);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<int, mixed>
     */
    private function columns(array $input): array
    {
        $slug = trim((string) ($input['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : slugify((string) ($input['title'] ?? ''));

        // Las viñetas vuelven a JSON, que es como viven en la columna.
        $deliverables = array_values(array_filter(
            array_map('strval', (array) ($input['deliverables'] ?? []))
        ));

        return [
            $slug,
            $this->fit((string) ($input['title'] ?? ''), 120),
            $this->fit((string) ($input['tagline'] ?? ''), 160),
            $this->fit((string) ($input['description'] ?? ''), 500),
            $deliverables === [] ? null : json_encode($deliverables, JSON_UNESCAPED_UNICODE),
            $this->fit((string) ($input['outcome'] ?? ''), 200),
            $this->fit((string) ($input['timeframe'] ?? ''), 60),
            (string) ($input['icon'] ?? 'spark'),
            (int) ($input['is_featured'] ?? 0),
            (int) ($input['is_published'] ?? 0),
        ];
    }
}
