<?php

namespace App\Models;

use App\Core\Model;

/**
 * Trayectoria laboral (timeline).
 */
class Experience extends Model
{
    /**
     * Del más reciente al más antiguo, por el intervalo del puesto.
     *
     * El que sigue en marcha no tiene fecha de fin, así que cuenta como que
     * termina hoy y encabeza el timeline. `started_on` desempata dos puestos
     * que acabaron el mismo día.
     */
    private const RECENT_FIRST = 'COALESCE(ended_on, CURDATE()) DESC, started_on DESC, id DESC';

    /**
     * «Sigue en marcha» no se guarda: es no tener fecha de fin.
     *
     * Guardarlo aparte (la vieja columna `is_current`) era repetir el mismo
     * hecho en dos sitios, y dos sitios se pueden contradecir: un puesto
     * marcado como actual con fecha de fin dejaba el timeline mintiendo.
     */
    private const IS_CURRENT = '(ended_on IS NULL) AS is_current';

    /**
     * Experiencias publicadas con sus logros ya hidratados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(?int $limit = null): array
    {
        $sql = 'SELECT id, company, role, location, employment_type, company_url,
                       summary, started_on, ended_on, ' . self::IS_CURRENT . '
                  FROM experiences
                 WHERE is_published = 1
              ORDER BY ' . self::RECENT_FIRST;

        $params = [];

        if ($limit !== null) {
            $sql     .= ' LIMIT ?';
            $params[] = $limit;
        }

        $experiences = $this->all($sql, $params);

        if ($experiences === []) {
            return [];
        }

        $highlights = $this->highlightsFor(
            array_map(static fn (array $e): int => (int) $e['id'], $experiences)
        );

        foreach ($experiences as &$experience) {
            $experience['highlights'] = $highlights[(int) $experience['id']] ?? [];
        }

        return $experiences;
    }

    /**
     * Puesto actual, para el hero de Inicio.
     *
     * @return array<string, mixed>|null
     */
    public function current(): ?array
    {
        return $this->one(
            'SELECT company, role, started_on
               FROM experiences
              WHERE is_published = 1 AND ended_on IS NULL
           ORDER BY started_on DESC
              LIMIT 1'
        );
    }

    /**
     * Año de inicio de la trayectoria (para "desde 20XX").
     */
    public function careerStartYear(): ?int
    {
        $row = $this->one('SELECT MIN(started_on) AS first_date FROM experiences WHERE is_published = 1');

        if (empty($row['first_date'])) {
            return null;
        }

        return (int) date('Y', strtotime((string) $row['first_date']));
    }

    /**
     * Etiqueta legible del tipo de contratación.
     */
    public static function employmentLabel(string $type): string
    {
        return self::TYPES[$type] ?? self::TYPES['tiempo_completo'];
    }

    /**
     * @param  array<int, int> $experienceIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function highlightsFor(array $experienceIds): array
    {
        $placeholders = implode(',', array_fill(0, count($experienceIds), '?'));

        $rows = $this->all(
            "SELECT experience_id, description
               FROM experience_highlights
              WHERE experience_id IN ($placeholders)
           ORDER BY id ASC",
            $experienceIds
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row['experience_id']][] = $row;
        }

        return $grouped;
    }

    // --------------------------------------------------------
    //  Panel de administración — publicadas y ocultas
    // --------------------------------------------------------

    /**
     * Los logros van como conteo, no hidratados: la tabla del panel dice
     * cuántos hay por puesto, y el texto de cada uno se lee al editarlo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminList(): array
    {
        return $this->all(
            'SELECT e.id, e.company, e.role, e.location, e.employment_type, e.company_url,
                    e.summary, e.started_on, e.ended_on, e.is_published, ' . self::IS_CURRENT . ',
                    (SELECT COUNT(*) FROM experience_highlights h WHERE h.experience_id = e.id) AS highlight_count
               FROM experiences e
           ORDER BY ' . self::RECENT_FIRST
        );
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /** Tipos de contratación del esquema, con su etiqueta. */
    public const TYPES = [
        'tiempo_completo' => 'Tiempo completo',
        'medio_tiempo'    => 'Medio tiempo',
        'freelance'       => 'Freelance',
        'practicas'       => 'Prácticas',
        'contrato'        => 'Por contrato',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM experiences WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * @return array<int, string>
     */
    public function highlightsOf(int $id): array
    {
        $rows = $this->all(
            'SELECT description FROM experience_highlights
              WHERE experience_id = ? ORDER BY id ASC',
            [$id]
        );

        return array_map(static fn (array $row): string => (string) $row['description'], $rows);
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input): array
    {
        $errors = [];

        $company = trim((string) ($input['company'] ?? ''));

        if ($company === '') {
            $errors['company'] = 'Falta la empresa.';
        } elseif (mb_strlen($company) > 140) {
            $errors['company'] = 'Máximo 140 caracteres.';
        }

        $role = trim((string) ($input['role'] ?? ''));

        if ($role === '') {
            $errors['role'] = 'Falta el puesto.';
        } elseif (mb_strlen($role) > 140) {
            $errors['role'] = 'Máximo 140 caracteres.';
        }

        if (! array_key_exists((string) ($input['employment_type'] ?? ''), self::TYPES)) {
            $errors['employment_type'] = 'Elige un tipo de contratación.';
        }

        $url = trim((string) ($input['company_url'] ?? ''));

        if ($url !== '' && ! $this->isUrl($url)) {
            $errors['company_url'] = 'La dirección debe empezar por http:// o https://.';
        }

        $start = trim((string) ($input['started_on'] ?? ''));
        $end   = trim((string) ($input['ended_on'] ?? ''));

        if ($start === '') {
            $errors['started_on'] = 'La fecha de inicio es obligatoria.';
        } elseif (! $this->isDate($start)) {
            $errors['started_on'] = 'Fecha no válida.';
        }

        if ($end !== '' && ! $this->isDate($end)) {
            $errors['ended_on'] = 'Fecha no válida.';
        } elseif ($end !== '' && $start !== '' && $errors === [] && $end < $start) {
            $errors['ended_on'] = 'El fin no puede ser anterior al inicio.';
        }

        foreach ((array) ($input['highlights'] ?? []) as $highlight) {
            if (mb_strlen((string) $highlight) > 400) {
                $errors['highlights'] = 'Cada logro cabe en 400 caracteres.';
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
            'INSERT INTO experiences
                (`company`, `role`, `location`, `employment_type`, `company_url`, `summary`,
                 `started_on`, `ended_on`, `is_published`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
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
            'UPDATE experiences SET
                `company` = ?, `role` = ?, `location` = ?, `employment_type` = ?,
                `company_url` = ?, `summary` = ?, `started_on` = ?, `ended_on` = ?,
                `is_published` = ?
              WHERE id = ?',
            $params
        );
    }

    /** Los logros se van con el puesto (ON DELETE CASCADE). */
    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM experiences WHERE id = ?', [$id]);
    }

    /**
     * Se reemplazan enteros: son cuatro viñetas por puesto y nadie apunta
     * a sus ids.
     *
     * @param array<int, string> $highlights
     */
    public function syncHighlights(int $id, array $highlights): void
    {
        $this->db()->execute('DELETE FROM experience_highlights WHERE experience_id = ?', [$id]);

        foreach ($highlights as $description) {
            $this->db()->execute(
                'INSERT INTO experience_highlights (experience_id, description) VALUES (?, ?)',
                [$id, mb_substr($description, 0, 400)]
            );
        }
    }

    /**
     * @param  array<string, mixed> $input
     * @return array<int, mixed>
     */
    private function columns(array $input): array
    {
        return [
            $this->fit((string) ($input['company'] ?? ''), 140),
            $this->fit((string) ($input['role'] ?? ''), 140),
            $this->fit((string) ($input['location'] ?? ''), 120),
            (string) ($input['employment_type'] ?? 'tiempo_completo'),
            $this->fit((string) ($input['company_url'] ?? ''), 255),
            $this->nullify((string) ($input['summary'] ?? '')),
            $this->nullify((string) ($input['started_on'] ?? '')),
            // Sin fecha de fin es lo que la landing lee como "Actual": no hay
            // nada más que guardar para decirlo.
            $this->nullify((string) ($input['ended_on'] ?? '')),
            (int) ($input['is_published'] ?? 0),
        ];
    }
}
