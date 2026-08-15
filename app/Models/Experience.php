<?php

namespace App\Models;

use App\Core\Model;

/**
 * Trayectoria laboral (timeline).
 */
class Experience extends Model
{
    /**
     * Experiencias publicadas con sus logros ya hidratados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(?int $limit = null): array
    {
        $sql = 'SELECT id, company, role, location, employment_type, company_url,
                       summary, started_on, ended_on, is_current
                  FROM experiences
                 WHERE is_published = 1
              ORDER BY sort_order ASC, started_on DESC';

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
              WHERE is_published = 1 AND is_current = 1
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
        return match ($type) {
            'medio_tiempo' => 'Medio tiempo',
            'freelance'    => 'Freelance',
            'practicas'    => 'Prácticas',
            'contrato'     => 'Por contrato',
            default        => 'Tiempo completo',
        };
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
           ORDER BY sort_order ASC, id ASC",
            $experienceIds
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row['experience_id']][] = $row;
        }

        return $grouped;
    }
}
