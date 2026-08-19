<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Technology;

/**
 * Stack: los chips de «Sobre mí» y los tags de cada proyecto.
 *
 * Borrar una tecnología la quita también de los proyectos que la usaban, así
 * que la pantalla de confirmación dice cuántos son antes de preguntar.
 */
class TechnologyController extends AdminController
{
    private const LIST = '/admin/tecnologias';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/technologies', [
            'technologies' => (new Technology())->adminList(),
        ], 'Stack');
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->form(null);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();

        $technology = (new Technology())->find((int) $id);

        if ($technology === null) {
            $this->missing(self::LIST, 'Esa tecnología');

            return;
        }

        $this->form($technology);
    }

    public function store(): void
    {
        $this->requireToken(self::LIST . '/nueva');

        $model  = new Technology();
        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nueva', $errors, $input);

            return;
        }

        $model->create($input);

        $this->flash('success', $input['name'] . ' añadida al stack.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $technologyId = (int) $id;

        $this->requireToken(self::LIST . '/' . $technologyId . '/editar');

        $model = new Technology();

        if ($model->find($technologyId) === null) {
            $this->missing(self::LIST, 'Esa tecnología');

            return;
        }

        $input  = $this->collect();
        $errors = $model->validate($input, $technologyId);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $technologyId . '/editar', $errors, $input);

            return;
        }

        $model->update($technologyId, $input);

        $this->flash('success', $input['name'] . ' guardada.');
        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $model      = new Technology();
        $technology = $model->find((int) $id);

        if ($technology === null) {
            $this->missing(self::LIST, 'Esa tecnología');

            return;
        }

        $used = $model->projectCount((int) $technology['id']);

        $this->render('admin/confirm', [
            'title'  => 'Borrar ' . $technology['name'],
            'lead'   => $used === 0
                ? 'No la usa ningún proyecto: se va limpia.'
                : 'Se quitará también del stack de ' . plural($used, 'proyecto', 'proyectos') . '.',
            'detail' => [
                'Categoría' => Technology::categoryLabel((string) $technology['category']),
                'Clave'     => (string) $technology['slug'],
                'Proyectos' => (string) $used,
            ],
            'action' => self::LIST . '/' . (int) $technology['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => $used === 0
                ? ''
                : 'Los proyectos no se borran: sólo pierden ese tag.',
        ], 'Borrar tecnología');
    }

    public function destroy(string $id): void
    {
        $technologyId = (int) $id;

        $this->requireToken(self::LIST);

        $model      = new Technology();
        $technology = $model->find($technologyId);

        if ($technology === null) {
            $this->missing(self::LIST, 'Esa tecnología');

            return;
        }

        $model->delete($technologyId);

        $this->flash('success', $technology['name'] . ' borrada del stack.');
        $this->redirect(self::LIST);
    }

    /**
     * @param array<string, mixed>|null $technology
     */
    private function form(?array $technology): void
    {
        [$errors, $old] = $this->takeFormState();

        $id = $technology === null ? 0 : (int) $technology['id'];

        $defaults = [
            'name'       => $technology['name']       ?? '',
            'slug'       => $technology['slug']       ?? '',
            'category'   => $technology['category']   ?? 'herramienta',
        ];

        $this->render('admin/technology-form', [
            'isNew'     => $technology === null,
            'action'    => $technology === null ? self::LIST : self::LIST . '/' . $id,
            'values'    => $this->values($defaults, $old),
            'errors'    => $errors,
            'deleteUrl' => $id > 0 ? self::LIST . '/' . $id . '/eliminar' : '',
            'back'      => self::LIST,
        ], $technology === null ? 'Nueva tecnología' : 'Editar tecnología');
    }

    /**
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'name'       => $this->postText('name'),
            'slug'       => $this->postText('slug'),
            'category'   => $this->postText('category'),
        ];
    }
}
