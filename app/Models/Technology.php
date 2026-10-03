<?php

namespace App\Models;

use App\Core\Model;

/**
 * Stack tecnológico: el stack de "Sobre mí" y las tecnologías de cada proyecto.
 */
class Technology extends Model
{
    /**
     * Todo el stack agrupado por categoría (para Sobre mí). `category` es un
     * ENUM: ordenar por él sigue el orden en que se declararon sus valores.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function groupedByCategory(): array
    {
        $rows = $this->all(
            'SELECT id, name, slug, category
               FROM technologies
           ORDER BY category ASC, name ASC'
        );

        return $this->groupBy($rows, 'category');
    }

    /**
     * Etiqueta legible de cada categoría.
     */
    public static function categoryLabel(string $category): string
    {
        return self::CATEGORIES[$category] ?? self::CATEGORIES['herramienta'];
    }

    // --------------------------------------------------------
    //  Panel de administración
    // --------------------------------------------------------

    /**
     * Stack completo con el número de proyectos que usa cada tecnología —
     * es lo que dice si un chip sigue ganándose su lugar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminList(): array
    {
        return $this->all(
            'SELECT t.id, t.name, t.slug, t.category, t.is_featured,
                    (SELECT COUNT(*) FROM project_technologies pt WHERE pt.technology_id = t.id) AS project_count
               FROM technologies t
           ORDER BY t.category ASC, t.name ASC'
        );
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /**
     * Categorías del esquema, con la etiqueta que ve el visitante. Van en el
     * mismo orden que el ENUM de `technologies.category`, que es el orden en
     * que MySQL las ordena y por tanto el que se ve en "Sobre mí".
     */
    public const CATEGORIES = [
        'datos'       => 'Datos',
        'sistemas'    => 'Sistemas y servidores',
        'redes'       => 'Redes',
        'seguridad'   => 'Seguridad',
        'cloud'       => 'Nube',
        'lenguaje'    => 'Lenguajes',
        'herramienta' => 'Herramientas',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM technologies WHERE id = ? LIMIT 1', [$id]);
    }

    /** En cuántos proyectos se usa: lo que hay que avisar antes de borrarla. */
    public function projectCount(int $id): int
    {
        $row = $this->one(
            'SELECT COUNT(*) AS total FROM project_technologies WHERE technology_id = ?',
            [$id]
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Todas, para el selector de stack del formulario de proyecto.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forPicker(): array
    {
        return $this->all(
            'SELECT id, name, category FROM technologies ORDER BY category ASC, name ASC'
        );
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input, ?int $id = null): array
    {
        $errors = [];

        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Falta el nombre.';
        } elseif (mb_strlen($name) > 60) {
            $errors['name'] = 'Máximo 60 caracteres.';
        }

        $slug = trim((string) ($input['slug'] ?? ''));

        if ($slug === '') {
            $slug = slugify($name);
        }

        if ($slug === '') {
            $errors['slug'] = 'No sale una clave del nombre: escríbela a mano.';
        } elseif (! $this->isSlug($slug)) {
            $errors['slug'] = 'Sólo minúsculas, números y guiones.';
        } elseif (mb_strlen($slug) > 60) {
            $errors['slug'] = 'Máximo 60 caracteres.';
        } elseif ($this->slugTaken('technologies', $slug, $id)) {
            $errors['slug'] = 'Ya hay otra tecnología con esa clave.';
        }

        if (! array_key_exists((string) ($input['category'] ?? ''), self::CATEGORIES)) {
            $errors['category'] = 'Elige una categoría.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function create(array $input): int
    {
        return $this->db()->execute(
            'INSERT INTO technologies (`name`, `slug`, `category`) VALUES (?, ?, ?)',
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
            'UPDATE technologies SET `name` = ?, `slug` = ?, `category` = ? WHERE id = ?',
            $params
        );
    }

    /**
     * Borrarla la quita también de los proyectos que la usaban
     * (ON DELETE CASCADE sobre `project_technologies`).
     */
    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM technologies WHERE id = ?', [$id]);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<int, mixed>
     */
    private function columns(array $input): array
    {
        $slug = trim((string) ($input['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : slugify((string) ($input['name'] ?? ''));

        return [
            $this->fit((string) ($input['name'] ?? ''), 60),
            $slug,
            (string) ($input['category'] ?? 'herramienta'),
        ];
    }
}
