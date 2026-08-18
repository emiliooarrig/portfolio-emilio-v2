<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Project;
use App\Models\Technology;

/**
 * Proyectos: el grid bento y el modal de detalle.
 *
 * Es la sección con más piezas colgando — stack, métricas y pasos de flujo —
 * y todas se guardan en el mismo envío que el proyecto: un formulario, un
 * botón. Nada de "guarda primero y luego añade las métricas".
 */
class ProjectController extends AdminController
{
    private const LIST = '/admin/proyectos';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/projects', [
            'projects' => (new Project())->adminList(),
        ], 'Proyectos');
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->form(null);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();

        $model   = new Project();
        $project = $model->find((int) $id);

        if ($project === null) {
            $this->missing(self::LIST, 'Ese proyecto');

            return;
        }

        $this->form($project);
    }

    public function store(): void
    {
        $this->requireToken(self::LIST . '/nuevo');

        $model  = new Project();
        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nuevo', $errors, $input);

            return;
        }

        $id = $model->create($input);
        $this->saveRelations($model, $id, $input);

        $this->flash('success', 'Proyecto «' . $input['title'] . '» creado.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $projectId = (int) $id;

        $this->requireToken(self::LIST . '/' . $projectId . '/editar');

        $model = new Project();

        if ($model->find($projectId) === null) {
            $this->missing(self::LIST, 'Ese proyecto');

            return;
        }

        $input  = $this->collect();
        $errors = $model->validate($input, $projectId);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $projectId . '/editar', $errors, $input);

            return;
        }

        $model->update($projectId, $input);
        $this->saveRelations($model, $projectId, $input);

        $this->flash('success', 'Proyecto «' . $input['title'] . '» guardado.');
        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $model   = new Project();
        $project = $model->find((int) $id);

        if ($project === null) {
            $this->missing(self::LIST, 'Ese proyecto');

            return;
        }

        $this->render('admin/confirm', [
            'title'  => 'Borrar «' . $project['title'] . '»',
            'lead'   => 'Se borra el proyecto y con él su stack, sus métricas y su flujo. No hay deshacer.',
            'detail' => [
                'URL'      => '/proyectos/' . $project['slug'],
                'Métricas' => (string) count($model->metricsOf((int) $project['id'])),
                'Flujo'    => plural(count($model->pipelineOf((int) $project['id'])), 'paso', 'pasos'),
            ],
            'action' => self::LIST . '/' . (int) $project['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => '¿Sólo quieres quitarlo del sitio? Edítalo y desmarca «Publicado»: sigue aquí y vuelve cuando quieras.',
        ], 'Borrar proyecto');
    }

    public function destroy(string $id): void
    {
        $projectId = (int) $id;

        $this->requireToken(self::LIST);

        $model   = new Project();
        $project = $model->find($projectId);

        if ($project === null) {
            $this->missing(self::LIST, 'Ese proyecto');

            return;
        }

        $model->delete($projectId);

        $this->flash('success', 'Proyecto «' . $project['title'] . '» borrado.');
        $this->redirect(self::LIST);
    }

    /**
     * El formulario, igual para alta y edición: cambia a dónde envía y qué
     * trae dentro.
     *
     * @param array<string, mixed>|null $project
     */
    private function form(?array $project): void
    {
        [$errors, $old] = $this->takeFormState();

        $model = new Project();
        $id    = $project === null ? 0 : (int) $project['id'];

        $defaults = [
            'title'        => $project['title']        ?? '',
            'slug'         => $project['slug']         ?? '',
            'subtitle'     => $project['subtitle']     ?? '',
            'summary'      => $project['summary']      ?? '',
            'context'      => $project['context']      ?? '',
            'solution'     => $project['solution']     ?? '',
            'outcome'      => $project['outcome']      ?? '',
            'role'         => $project['role']         ?? '',
            'client'       => $project['client']       ?? '',
            'cover_image'  => $project['cover_image']  ?? '',
            'repo_url'     => $project['repo_url']     ?? '',
            'demo_url'     => $project['demo_url']     ?? '',
            'bento_size'   => $project['bento_size']   ?? 'md',
            'started_on'   => $project['started_on']   ?? '',
            'ended_on'     => $project['ended_on']     ?? '',
            'sort_order'   => $project['sort_order']   ?? $this->nextOrder(),
            'is_featured'  => (int) ($project['is_featured'] ?? 0),
            'is_published' => (int) ($project['is_published'] ?? 1),
            'has_pipeline' => (int) ($project['has_pipeline'] ?? 0),
        ];

        $this->render('admin/project-form', [
            'isNew'        => $project === null,
            'action'       => $project === null ? self::LIST : self::LIST . '/' . $id,
            'values'       => $this->values($defaults, $old),
            'errors'       => $errors,
            'technologies' => (new Technology())->forPicker(),
            'selected'     => $old['technologies'] ?? ($id > 0 ? $model->technologyIds($id) : []),
            'metrics'      => $old['metrics'] ?? ($id > 0 ? $model->metricsOf($id) : []),
            'pipeline'     => $old['pipeline'] ?? ($id > 0 ? $model->pipelineOf($id) : []),
            'deleteUrl'    => $id > 0 ? self::LIST . '/' . $id . '/eliminar' : '',
            'back'         => self::LIST,
        ], $project === null ? 'Nuevo proyecto' : 'Editar proyecto');
    }

    /**
     * Todo lo que el formulario manda, en un solo array: es lo que se valida,
     * lo que se guarda y lo que se devuelve al formulario si algo falla.
     *
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'title'        => $this->postText('title'),
            'slug'         => $this->postText('slug'),
            'subtitle'     => $this->postText('subtitle'),
            'summary'      => $this->postText('summary'),
            'context'      => $this->postText('context'),
            'solution'     => $this->postText('solution'),
            'outcome'      => $this->postText('outcome'),
            'role'         => $this->postText('role'),
            'client'       => $this->postText('client'),
            'cover_image'  => $this->postText('cover_image'),
            'repo_url'     => $this->postText('repo_url'),
            'demo_url'     => $this->postText('demo_url'),
            'bento_size'   => $this->postText('bento_size'),
            'started_on'   => $this->postText('started_on'),
            'ended_on'     => $this->postText('ended_on'),
            'sort_order'   => $this->postInt('sort_order'),
            'is_featured'  => $this->postFlag('is_featured'),
            'is_published' => $this->postFlag('is_published'),
            'has_pipeline' => $this->postFlag('has_pipeline'),
            'technologies' => $this->postIds('technologies'),
            'metrics'      => $this->postRows('metrics', ['label', 'value', 'unit']),
            'pipeline'     => $this->postRows('pipeline', ['label', 'description', 'stage']),
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    private function saveRelations(Project $model, int $id, array $input): void
    {
        $model->syncTechnologies($id, $input['technologies']);
        $model->syncMetrics($id, $input['metrics']);
        $model->syncPipeline($id, $input['pipeline']);
    }

    /** Un proyecto nuevo entra al final, no en medio del orden ya decidido. */
    private function nextOrder(): int
    {
        $projects = (new Project())->adminList();
        $orders   = array_map(static fn (array $p): int => (int) $p['sort_order'], $projects);

        return $orders === [] ? 10 : max($orders) + 10;
    }
}
