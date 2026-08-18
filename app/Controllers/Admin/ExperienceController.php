<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Experience;

/**
 * Experiencia: el timeline laboral.
 *
 * Es la sección donde el lenguaje técnico se queda como está: aquí lo lee
 * quien lo entiende, y suavizarlo quitaría justo la prueba de que sé hacerlo.
 */
class ExperienceController extends AdminController
{
    private const LIST = '/admin/experiencia';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/experiences', [
            'experiences' => (new Experience())->adminList(),
        ], 'Experiencia');
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->form(null);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();

        $experience = (new Experience())->find((int) $id);

        if ($experience === null) {
            $this->missing(self::LIST, 'Ese puesto');

            return;
        }

        $this->form($experience);
    }

    public function store(): void
    {
        $this->requireToken(self::LIST . '/nuevo');

        $model  = new Experience();
        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nuevo', $errors, $input);

            return;
        }

        $id = $model->create($input);
        $model->syncHighlights($id, $input['highlights']);

        $this->flash('success', 'Puesto en ' . $input['company'] . ' creado.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $experienceId = (int) $id;

        $this->requireToken(self::LIST . '/' . $experienceId . '/editar');

        $model = new Experience();

        if ($model->find($experienceId) === null) {
            $this->missing(self::LIST, 'Ese puesto');

            return;
        }

        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $experienceId . '/editar', $errors, $input);

            return;
        }

        $model->update($experienceId, $input);
        $model->syncHighlights($experienceId, $input['highlights']);

        $this->flash('success', 'Puesto en ' . $input['company'] . ' guardado.');
        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $model      = new Experience();
        $experience = $model->find((int) $id);

        if ($experience === null) {
            $this->missing(self::LIST, 'Ese puesto');

            return;
        }

        $this->render('admin/confirm', [
            'title'  => 'Borrar el puesto en ' . $experience['company'],
            'lead'   => 'Se borra el puesto y con él sus logros. No hay deshacer.',
            'detail' => [
                'Puesto'  => (string) $experience['role'],
                'Periodo' => date_range($experience['started_on'], $experience['ended_on']),
                'Logros'  => (string) count($model->highlightsOf((int) $experience['id'])),
            ],
            'action' => self::LIST . '/' . (int) $experience['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => 'Un puesto viejo no estorba: desmarca «Publicado» si sólo quieres acortar el timeline.',
        ], 'Borrar puesto');
    }

    public function destroy(string $id): void
    {
        $experienceId = (int) $id;

        $this->requireToken(self::LIST);

        $model      = new Experience();
        $experience = $model->find($experienceId);

        if ($experience === null) {
            $this->missing(self::LIST, 'Ese puesto');

            return;
        }

        $model->delete($experienceId);

        $this->flash('success', 'Puesto en ' . $experience['company'] . ' borrado.');
        $this->redirect(self::LIST);
    }

    /**
     * @param array<string, mixed>|null $experience
     */
    private function form(?array $experience): void
    {
        [$errors, $old] = $this->takeFormState();

        $model = new Experience();
        $id    = $experience === null ? 0 : (int) $experience['id'];

        $defaults = [
            'company'         => $experience['company']         ?? '',
            'role'            => $experience['role']            ?? '',
            'location'        => $experience['location']        ?? '',
            'employment_type' => $experience['employment_type'] ?? 'tiempo_completo',
            'company_url'     => $experience['company_url']     ?? '',
            'summary'         => $experience['summary']         ?? '',
            'started_on'      => $experience['started_on']      ?? '',
            'ended_on'        => $experience['ended_on']        ?? '',
            'sort_order'      => $experience['sort_order']      ?? $this->nextOrder(),
            'is_current'      => (int) ($experience['is_current'] ?? 0),
            'is_published'    => (int) ($experience['is_published'] ?? 1),
            'highlights'      => $id > 0 ? implode("\n", $model->highlightsOf($id)) : '',
        ];

        if (isset($old['highlights']) && is_array($old['highlights'])) {
            $old['highlights'] = implode("\n", $old['highlights']);
        }

        $this->render('admin/experience-form', [
            'isNew'     => $experience === null,
            'action'    => $experience === null ? self::LIST : self::LIST . '/' . $id,
            'values'    => $this->values($defaults, $old),
            'errors'    => $errors,
            'deleteUrl' => $id > 0 ? self::LIST . '/' . $id . '/eliminar' : '',
            'back'      => self::LIST,
        ], $experience === null ? 'Nuevo puesto' : 'Editar puesto');
    }

    /**
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'company'         => $this->postText('company'),
            'role'            => $this->postText('role'),
            'location'        => $this->postText('location'),
            'employment_type' => $this->postText('employment_type'),
            'company_url'     => $this->postText('company_url'),
            'summary'         => $this->postText('summary'),
            'started_on'      => $this->postText('started_on'),
            'ended_on'        => $this->postText('ended_on'),
            'sort_order'      => $this->postInt('sort_order'),
            'is_current'      => $this->postFlag('is_current'),
            'is_published'    => $this->postFlag('is_published'),
            'highlights'      => $this->postLines('highlights'),
        ];
    }

    private function nextOrder(): int
    {
        $orders = array_map(
            static fn (array $e): int => (int) $e['sort_order'],
            (new Experience())->adminList()
        );

        return $orders === [] ? 10 : max($orders) + 10;
    }
}
