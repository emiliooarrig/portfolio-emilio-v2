<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Upload;
use App\Models\Project;
use App\Models\Technology;

/**
 * Proyectos: el índice de la landing y el modal de detalle.
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
        $this->guardUploadSize(self::LIST . '/nuevo');
        $this->requireToken(self::LIST . '/nuevo');

        $model  = new Project();
        $input  = $this->collect();
        $files  = $this->files($input['title']);
        $input  = $this->keepFiles($input, $files);
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nuevo', $errors, $input);

            return;
        }

        $saved = $this->storeFiles($input, $files);

        if ($saved['errors'] !== []) {
            $this->backWithErrors(self::LIST . '/nuevo', $saved['errors'], $input);

            return;
        }

        $id = $model->create($saved['input']);
        $this->saveRelations($model, $id, $saved['input']);

        $this->flash('success', 'Proyecto «' . $input['title'] . '» creado.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $projectId = (int) $id;

        $this->guardUploadSize(self::LIST . '/' . $projectId . '/editar');
        $this->requireToken(self::LIST . '/' . $projectId . '/editar');

        $model   = new Project();
        $current = $model->find($projectId);

        if ($current === null) {
            $this->missing(self::LIST, 'Ese proyecto');

            return;
        }

        $input  = $this->collect();
        $files  = $this->files($input['title']);
        $input  = $this->keepFiles($input, $files, $current);
        $errors = $model->validate($input, $projectId);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $projectId . '/editar', $errors, $input);

            return;
        }

        $saved = $this->storeFiles($input, $files);

        if ($saved['errors'] !== []) {
            $this->backWithErrors(self::LIST . '/' . $projectId . '/editar', $saved['errors'], $input);

            return;
        }

        $model->update($projectId, $saved['input']);
        $this->saveRelations($model, $projectId, $saved['input']);
        $this->dropFiles($files, $saved['discarded']);

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

        // El borrado es definitivo: sin fila que la apunte, la portada sólo
        // ocuparía espacio en /public.
        Upload::image()->remove((string) ($project['cover_image'] ?? ''));

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
            'started_on'   => $project['started_on']   ?? '',
            'ended_on'     => $project['ended_on']     ?? '',
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
            // Formatos y peso los decide la regla de subida, no la vista.
            'coverRules'   => Upload::image()->rules(),
        ], $project === null ? 'Nuevo proyecto' : 'Editar proyecto');
    }

    /**
     * El único archivo del formulario: la portada. Se guarda con el nombre
     * del proyecto por delante para que la carpeta se pueda leer.
     *
     * @return array<string, array{field: string, upload: Upload, name: string}>
     */
    private function files(string $title): array
    {
        return [
            'cover_image' => [
                'field'  => 'cover_image',
                'upload' => Upload::image(),
                'name'   => 'portada-' . $title,
            ],
        ];
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
            // `cover_image` no se teclea: la pone `store()`/`update()` a
            // partir del archivo que se suba (o de la que ya estaba).
            'repo_url'     => $this->postText('repo_url'),
            'demo_url'     => $this->postText('demo_url'),
            'started_on'   => $this->postText('started_on'),
            'ended_on'     => $this->postText('ended_on'),
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
}
