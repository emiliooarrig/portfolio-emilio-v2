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
     * Pipeline genérico del Inicio (registros sin proyecto asociado).
     *
     * @return array<int, array<string, mixed>>
     */
    public function generalPipeline(): array
    {
        return $this->all(
            'SELECT label, description, stage
               FROM project_pipeline_steps
              WHERE project_id IS NULL
           ORDER BY sort_order ASC, id ASC'
        );
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
}
