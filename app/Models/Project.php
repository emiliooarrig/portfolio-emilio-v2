<?php

namespace App\Models;

use App\Core\Model;

/**
 * Proyectos destacados: grid bento y vista de detalle.
 */
class Project extends Model
{
    /**
     * Proyectos publicados con su stack ya hidratado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(?int $limit = null): array
    {
        $sql = 'SELECT id, slug, title, subtitle, summary, cover_image, bento_size,
                       is_featured, started_on, ended_on
                  FROM projects
                 WHERE is_published = 1
              ORDER BY is_featured DESC, sort_order ASC, id ASC';

        $params = [];

        if ($limit !== null) {
            $sql     .= ' LIMIT ?';
            $params[] = $limit;
        }

        return $this->withTechnologies($this->all($sql, $params));
    }

    /**
     * Proyecto completo por slug: stack, métricas y pipeline.
     *
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $project = $this->one(
            'SELECT * FROM projects WHERE slug = ? AND is_published = 1 LIMIT 1',
            [$slug]
        );

        if ($project === null) {
            return null;
        }

        $id = (int) $project['id'];

        $project['technologies'] = $this->technologiesFor([$id])[$id] ?? [];
        $project['metrics']      = $this->metricsFor($id);
        $project['pipeline']     = $this->pipelineFor($id);

        return $project;
    }

    /**
     * Vecinos para navegar entre proyectos desde el detalle.
     *
     * @return array{prev: array<string, mixed>|null, next: array<string, mixed>|null}
     */
    public function neighbours(int $sortOrder, int $id): array
    {
        return [
            'prev' => $this->one(
                'SELECT slug, title FROM projects
                  WHERE is_published = 1 AND (sort_order < ? OR (sort_order = ? AND id < ?))
               ORDER BY sort_order DESC, id DESC LIMIT 1',
                [$sortOrder, $sortOrder, $id]
            ),
            'next' => $this->one(
                'SELECT slug, title FROM projects
                  WHERE is_published = 1 AND (sort_order > ? OR (sort_order = ? AND id > ?))
               ORDER BY sort_order ASC, id ASC LIMIT 1',
                [$sortOrder, $sortOrder, $id]
            ),
        ];
    }

    public function countPublished(): int
    {
        $row = $this->one('SELECT COUNT(*) AS total FROM projects WHERE is_published = 1');

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @param  array<int, array<string, mixed>> $projects
     * @return array<int, array<string, mixed>>
     */
    private function withTechnologies(array $projects): array
    {
        if ($projects === []) {
            return [];
        }

        $ids  = array_map(static fn (array $p): int => (int) $p['id'], $projects);
        $tech = $this->technologiesFor($ids);

        foreach ($projects as &$project) {
            $project['technologies'] = $tech[(int) $project['id']] ?? [];
        }

        return $projects;
    }

    /**
     * @param  array<int, int> $projectIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function technologiesFor(array $projectIds): array
    {
        if ($projectIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));

        $rows = $this->all(
            "SELECT pt.project_id, t.name, t.slug, t.category
               FROM project_technologies pt
               JOIN technologies t ON t.id = pt.technology_id
              WHERE pt.project_id IN ($placeholders)
           ORDER BY pt.sort_order ASC, t.name ASC",
            $projectIds
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row['project_id']][] = $row;
        }

        return $grouped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function metricsFor(int $projectId): array
    {
        return $this->all(
            'SELECT label, value, unit
               FROM project_metrics
              WHERE project_id = ?
           ORDER BY sort_order ASC, id ASC',
            [$projectId]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pipelineFor(int $projectId): array
    {
        return $this->all(
            'SELECT label, description, stage
               FROM project_pipeline_steps
              WHERE project_id = ?
           ORDER BY sort_order ASC, id ASC',
            [$projectId]
        );
    }

    // --------------------------------------------------------
    //  Panel de administración
    //
    //  La landing sólo pide lo publicado; el panel necesita ver
    //  también lo oculto — si no, lo despublicado desaparecería
    //  de la única pantalla desde la que se puede recuperar.
    // --------------------------------------------------------

    /**
     * Catálogo completo para la tabla del panel: con el stack hidratado y
     * el conteo de lo que cuelga de cada proyecto.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminList(): array
    {
        $rows = $this->all(
            'SELECT p.id, p.slug, p.title, p.subtitle, p.bento_size, p.is_featured,
                    p.is_published, p.has_pipeline, p.sort_order, p.started_on,
                    p.ended_on, p.updated_at,
                    (SELECT COUNT(*) FROM project_metrics m WHERE m.project_id = p.id)        AS metric_count,
                    (SELECT COUNT(*) FROM project_pipeline_steps s WHERE s.project_id = p.id) AS step_count
               FROM projects p
           ORDER BY p.sort_order ASC, p.id ASC'
        );

        return $this->withTechnologies($rows);
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /** Tamaños de celda en el bento de 12 columnas. */
    public const SIZES = ['sm' => 'Pequeña', 'md' => 'Media', 'lg' => 'Grande', 'xl' => 'Destacada'];

    /** Etapas del riel de flujo: crudo → transformado → refinado. */
    public const STAGES = ['raw' => 'Dato crudo', 'transform' => 'Transformación', 'refined' => 'Dato refinado'];

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM projects WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Errores por campo; vacío = se puede guardar.
     *
     * @param  array<string, mixed> $input
     * @param  int|null             $id     la fila que se edita, si es edición
     * @return array<string, string>
     */
    public function validate(array $input, ?int $id = null): array
    {
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));

        if ($title === '') {
            $errors['title'] = 'El proyecto necesita un título.';
        } elseif (mb_strlen($title) > 160) {
            $errors['title'] = 'Máximo 160 caracteres.';
        }

        $slug = trim((string) ($input['slug'] ?? ''));

        if ($slug === '') {
            $slug = slugify($title);
        }

        if ($slug === '') {
            $errors['slug'] = 'No sale una URL del título: escríbela a mano.';
        } elseif (! $this->isSlug($slug)) {
            $errors['slug'] = 'Sólo minúsculas, números y guiones.';
        } elseif (mb_strlen($slug) > 120) {
            $errors['slug'] = 'Máximo 120 caracteres.';
        } elseif ($this->slugTaken('projects', $slug, $id)) {
            $errors['slug'] = 'Ya hay otro proyecto con esa URL.';
        }

        $summary = trim((string) ($input['summary'] ?? ''));

        if ($summary === '') {
            $errors['summary'] = 'Escribe el resumen que se lee en la tarjeta.';
        } elseif (mb_strlen($summary) > 400) {
            $errors['summary'] = 'Máximo 400 caracteres — es el texto de la tarjeta, no el del detalle.';
        }

        if (mb_strlen(trim((string) ($input['subtitle'] ?? ''))) > 200) {
            $errors['subtitle'] = 'Máximo 200 caracteres.';
        }

        if (! array_key_exists((string) ($input['bento_size'] ?? ''), self::SIZES)) {
            $errors['bento_size'] = 'Elige un tamaño de celda.';
        }

        foreach (['repo_url' => 'repositorio', 'demo_url' => 'demo'] as $field => $label) {
            $url = trim((string) ($input[$field] ?? ''));

            if ($url !== '' && ! $this->isUrl($url)) {
                $errors[$field] = 'La dirección del ' . $label . ' debe empezar por http:// o https://.';
            }
        }

        $start = trim((string) ($input['started_on'] ?? ''));
        $end   = trim((string) ($input['ended_on'] ?? ''));

        if ($start !== '' && ! $this->isDate($start)) {
            $errors['started_on'] = 'Fecha no válida.';
        }

        if ($end !== '' && ! $this->isDate($end)) {
            $errors['ended_on'] = 'Fecha no válida.';
        }

        if ($start !== '' && $end !== '' && $errors === [] && $end < $start) {
            $errors['ended_on'] = 'El fin no puede ser anterior al inicio.';
        }

        // Las filas llegan ya filtradas: si tienen etiqueta, les falta el resto.
        foreach ((array) ($input['metrics'] ?? []) as $metric) {
            if (trim((string) ($metric['value'] ?? '')) === '') {
                $errors['metrics'] = 'Cada métrica con etiqueta necesita su número.';
                break;
            }
        }

        foreach ((array) ($input['pipeline'] ?? []) as $step) {
            if (! array_key_exists((string) ($step['stage'] ?? ''), self::STAGES)) {
                $errors['pipeline'] = 'Cada paso del flujo necesita su etapa.';
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
            'INSERT INTO projects
                (`slug`, `title`, `subtitle`, `summary`, `context`, `solution`, `outcome`,
                 `role`, `client`, `cover_image`, `repo_url`, `demo_url`, `bento_size`,
                 `is_featured`, `is_published`, `has_pipeline`, `started_on`, `ended_on`, `sort_order`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
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
            'UPDATE projects SET
                `slug` = ?, `title` = ?, `subtitle` = ?, `summary` = ?, `context` = ?,
                `solution` = ?, `outcome` = ?, `role` = ?, `client` = ?, `cover_image` = ?,
                `repo_url` = ?, `demo_url` = ?, `bento_size` = ?, `is_featured` = ?,
                `is_published` = ?, `has_pipeline` = ?, `started_on` = ?, `ended_on` = ?,
                `sort_order` = ?
              WHERE id = ?',
            $params
        );
    }

    /** El stack, las métricas y el flujo se van con él (ON DELETE CASCADE). */
    public function delete(int $id): void
    {
        $this->db()->execute('DELETE FROM projects WHERE id = ?', [$id]);
    }

    /**
     * @return array<int, int>
     */
    public function technologyIds(int $id): array
    {
        $rows = $this->all(
            'SELECT technology_id FROM project_technologies WHERE project_id = ? ORDER BY sort_order ASC',
            [$id]
        );

        return array_map(static fn (array $row): int => (int) $row['technology_id'], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function metricsOf(int $id): array
    {
        return $this->metricsFor($id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pipelineOf(int $id): array
    {
        return $this->pipelineFor($id);
    }

    /**
     * Se borra y se vuelve a insertar en vez de comparar diferencias: son
     * tres o cuatro filas por proyecto y nada apunta a sus ids.
     *
     * @param array<int, int> $technologyIds
     */
    public function syncTechnologies(int $id, array $technologyIds): void
    {
        $this->db()->execute('DELETE FROM project_technologies WHERE project_id = ?', [$id]);

        foreach (array_values($technologyIds) as $order => $technologyId) {
            $this->db()->execute(
                'INSERT INTO project_technologies (project_id, technology_id, sort_order) VALUES (?, ?, ?)',
                [$id, $technologyId, ($order + 1) * 10]
            );
        }
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    public function syncMetrics(int $id, array $rows): void
    {
        $this->db()->execute('DELETE FROM project_metrics WHERE project_id = ?', [$id]);

        foreach (array_values($rows) as $order => $row) {
            $this->db()->execute(
                'INSERT INTO project_metrics (project_id, label, value, unit, sort_order) VALUES (?, ?, ?, ?, ?)',
                [
                    $id,
                    $this->fit($row['label'] ?? '', 80),
                    $this->fit($row['value'] ?? '', 40),
                    $this->fit($row['unit'] ?? '', 20),
                    ($order + 1) * 10,
                ]
            );
        }
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    public function syncPipeline(int $id, array $rows): void
    {
        $this->db()->execute('DELETE FROM project_pipeline_steps WHERE project_id = ?', [$id]);

        foreach (array_values($rows) as $order => $row) {
            $this->db()->execute(
                'INSERT INTO project_pipeline_steps (project_id, label, description, stage, sort_order)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $id,
                    $this->fit($row['label'] ?? '', 60),
                    $this->fit($row['description'] ?? '', 200),
                    (string) ($row['stage'] ?? 'raw'),
                    ($order + 1) * 10,
                ]
            );
        }
    }

    /**
     * Valores en el orden en que los esperan el INSERT y el UPDATE — una
     * sola lista para los dos, que es justo donde se colaría un desajuste.
     *
     * @param  array<string, mixed> $input
     * @return array<int, mixed>
     */
    private function columns(array $input): array
    {
        $slug = trim((string) ($input['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : slugify((string) ($input['title'] ?? ''));

        return [
            $slug,
            $this->fit((string) ($input['title'] ?? ''), 160),
            $this->fit((string) ($input['subtitle'] ?? ''), 200),
            $this->fit((string) ($input['summary'] ?? ''), 400),
            $this->nullify((string) ($input['context'] ?? '')),
            $this->nullify((string) ($input['solution'] ?? '')),
            $this->nullify((string) ($input['outcome'] ?? '')),
            $this->fit((string) ($input['role'] ?? ''), 120),
            $this->fit((string) ($input['client'] ?? ''), 120),
            $this->fit((string) ($input['cover_image'] ?? ''), 255),
            $this->fit((string) ($input['repo_url'] ?? ''), 255),
            $this->fit((string) ($input['demo_url'] ?? ''), 255),
            (string) ($input['bento_size'] ?? 'md'),
            (int) ($input['is_featured'] ?? 0),
            (int) ($input['is_published'] ?? 0),
            (int) ($input['has_pipeline'] ?? 0),
            $this->nullify((string) ($input['started_on'] ?? '')),
            $this->nullify((string) ($input['ended_on'] ?? '')),
            (int) ($input['sort_order'] ?? 0),
        ];
    }
}
