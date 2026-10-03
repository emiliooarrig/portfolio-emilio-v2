<?php

namespace App\Models;

use App\Core\Model;

/**
 * Proyectos: índice de la landing y vista de detalle.
 */
class Project extends Model
{
    /**
     * Del más reciente al más antiguo, por el intervalo del proyecto.
     *
     * Lo que sigue abierto no tiene fecha de fin, así que cuenta como que
     * termina hoy: es lo más reciente que hay y encabeza la lista. Los
     * proyectos sin ninguna fecha caen al final, que es donde se nota que
     * les falta el dato en vez de colarse entre los de este año.
     */
    private const RECENT_FIRST = '(started_on IS NULL AND ended_on IS NULL) ASC,
                                  COALESCE(ended_on, CURDATE()) DESC,
                                  started_on DESC,
                                  id DESC';

    /**
     * Proyectos publicados con su stack ya hidratado y, en los destacados,
     * su métrica principal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(?int $limit = null): array
    {
        $sql = 'SELECT id, slug, title, subtitle, summary, cover_image,
                       is_featured, started_on, ended_on
                  FROM projects
                 WHERE is_published = 1
              ORDER BY ' . self::RECENT_FIRST;

        $params = [];

        if ($limit !== null) {
            $sql     .= ' LIMIT ?';
            $params[] = $limit;
        }

        return $this->withLeadMetric($this->withTechnologies($this->all($sql, $params)));
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
     * Primera métrica (la de menor id) de cada proyecto destacado, en una
     * sola consulta. Es la que la fila ampliada muestra en grande.
     *
     * @param  array<int, array<string, mixed>> $projects
     * @return array<int, array<string, mixed>>
     */
    private function withLeadMetric(array $projects): array
    {
        $ids = [];

        foreach ($projects as $project) {
            if (! empty($project['is_featured'])) {
                $ids[] = (int) $project['id'];
            }
        }

        $metrics = [];

        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $rows = $this->all(
                "SELECT m.project_id, m.label, m.value, m.unit
                   FROM project_metrics m
                   JOIN (SELECT project_id, MIN(id) AS id
                           FROM project_metrics
                          WHERE project_id IN ($placeholders)
                       GROUP BY project_id) first ON first.id = m.id",
                $ids
            );

            foreach ($rows as $row) {
                $metrics[(int) $row['project_id']] = $row;
            }
        }

        foreach ($projects as &$project) {
            $project['lead_metric'] = $metrics[(int) $project['id']] ?? null;
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
           ORDER BY t.name ASC",
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
           ORDER BY id ASC',
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
           ORDER BY id ASC',
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
            'SELECT p.id, p.slug, p.title, p.subtitle, p.is_featured,
                    p.is_published, p.has_pipeline, p.started_on,
                    p.ended_on, p.updated_at,
                    (SELECT COUNT(*) FROM project_metrics m WHERE m.project_id = p.id)        AS metric_count,
                    (SELECT COUNT(*) FROM project_pipeline_steps s WHERE s.project_id = p.id) AS step_count
               FROM projects p
           ORDER BY ' . self::RECENT_FIRST
        );

        return $this->withTechnologies($rows);
    }

    // --------------------------------------------------------
    //  Panel: escritura
    // --------------------------------------------------------

    /** Etapas de cada paso del flujo (columna `stage`; el detalle sólo muestra el orden). */
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
            $errors['summary'] = 'Escribe el resumen: es lo que se lee al compartir el enlace del proyecto.';
        } elseif (mb_strlen($summary) > 400) {
            $errors['summary'] = 'Máximo 400 caracteres: es la descripción al compartir, no el detalle.';
        }

        if (mb_strlen(trim((string) ($input['subtitle'] ?? ''))) > 200) {
            $errors['subtitle'] = 'Máximo 200 caracteres.';
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
                 `role`, `client`, `cover_image`, `repo_url`, `demo_url`,
                 `is_featured`, `is_published`, `has_pipeline`, `started_on`, `ended_on`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
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
                `repo_url` = ?, `demo_url` = ?, `is_featured` = ?,
                `is_published` = ?, `has_pipeline` = ?, `started_on` = ?, `ended_on` = ?
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
            'SELECT technology_id FROM project_technologies WHERE project_id = ? ORDER BY technology_id ASC',
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

        foreach ($technologyIds as $technologyId) {
            $this->db()->execute(
                'INSERT INTO project_technologies (project_id, technology_id) VALUES (?, ?)',
                [$id, $technologyId]
            );
        }
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    public function syncMetrics(int $id, array $rows): void
    {
        $this->db()->execute('DELETE FROM project_metrics WHERE project_id = ?', [$id]);

        foreach ($rows as $row) {
            $this->db()->execute(
                'INSERT INTO project_metrics (project_id, label, value, unit) VALUES (?, ?, ?, ?)',
                [
                    $id,
                    $this->fit($row['label'] ?? '', 80),
                    $this->fit($row['value'] ?? '', 40),
                    $this->fit($row['unit'] ?? '', 20),
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

        foreach ($rows as $row) {
            $this->db()->execute(
                'INSERT INTO project_pipeline_steps (project_id, label, description, stage)
                 VALUES (?, ?, ?, ?)',
                [
                    $id,
                    $this->fit($row['label'] ?? '', 60),
                    $this->fit($row['description'] ?? '', 200),
                    (string) ($row['stage'] ?? 'raw'),
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
            (int) ($input['is_featured'] ?? 0),
            (int) ($input['is_published'] ?? 0),
            (int) ($input['has_pipeline'] ?? 0),
            $this->nullify((string) ($input['started_on'] ?? '')),
            $this->nullify((string) ($input['ended_on'] ?? '')),
        ];
    }
}
