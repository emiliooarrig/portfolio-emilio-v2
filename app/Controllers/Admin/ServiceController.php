<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Service;

/**
 * Servicios: las tarjetas del carrusel.
 *
 * Lo que se escribe aquí lo lee un cliente que no es de sistemas. El
 * formulario lo recuerda en sus pistas, porque es la regla que más fácil se
 * rompe cuando uno escribe deprisa.
 */
class ServiceController extends AdminController
{
    private const LIST = '/admin/servicios';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/services', [
            'services' => (new Service())->adminList(),
        ], 'Servicios');
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->form(null);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();

        $service = (new Service())->find((int) $id);

        if ($service === null) {
            $this->missing(self::LIST, 'Ese servicio');

            return;
        }

        $this->form($service);
    }

    public function store(): void
    {
        $this->requireToken(self::LIST . '/nuevo');

        $model  = new Service();
        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nuevo', $errors, $input);

            return;
        }

        $model->create($input);

        $this->flash('success', 'Servicio «' . $input['title'] . '» creado.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $serviceId = (int) $id;

        $this->requireToken(self::LIST . '/' . $serviceId . '/editar');

        $model = new Service();

        if ($model->find($serviceId) === null) {
            $this->missing(self::LIST, 'Ese servicio');

            return;
        }

        $input  = $this->collect();
        $errors = $model->validate($input, $serviceId);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $serviceId . '/editar', $errors, $input);

            return;
        }

        $model->update($serviceId, $input);

        $this->flash('success', 'Servicio «' . $input['title'] . '» guardado.');
        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $service = (new Service())->find((int) $id);

        if ($service === null) {
            $this->missing(self::LIST, 'Ese servicio');

            return;
        }

        $this->render('admin/confirm', [
            'title'  => 'Borrar «' . $service['title'] . '»',
            'lead'   => 'Desaparece del carrusel de servicios. No hay deshacer.',
            'detail' => [
                'Clave'       => (string) $service['slug'],
                'Entregables' => plural(count($service['deliverables']), 'viñeta', 'viñetas'),
            ],
            'action' => self::LIST . '/' . (int) $service['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => '¿Sólo quieres esconderlo? Edítalo y desmarca «Publicado».',
        ], 'Borrar servicio');
    }

    public function destroy(string $id): void
    {
        $serviceId = (int) $id;

        $this->requireToken(self::LIST);

        $model   = new Service();
        $service = $model->find($serviceId);

        if ($service === null) {
            $this->missing(self::LIST, 'Ese servicio');

            return;
        }

        $model->delete($serviceId);

        $this->flash('success', 'Servicio «' . $service['title'] . '» borrado.');
        $this->redirect(self::LIST);
    }

    /**
     * @param array<string, mixed>|null $service
     */
    private function form(?array $service): void
    {
        [$errors, $old] = $this->takeFormState();

        $id = $service === null ? 0 : (int) $service['id'];

        $defaults = [
            'title'        => $service['title']     ?? '',
            'slug'         => $service['slug']      ?? '',
            'tagline'      => $service['tagline']   ?? '',
            'description'  => $service['description'] ?? '',
            'outcome'      => $service['outcome']   ?? '',
            'timeframe'    => $service['timeframe'] ?? '',
            'icon'         => $service['icon']      ?? 'spark',
            'sort_order'   => $service['sort_order'] ?? $this->nextOrder(),
            'is_featured'  => (int) ($service['is_featured'] ?? 0),
            'is_published' => (int) ($service['is_published'] ?? 1),
            // Las viñetas se editan una por línea; en la base viven en JSON.
            'deliverables' => implode("\n", $service['deliverables'] ?? []),
        ];

        if (isset($old['deliverables']) && is_array($old['deliverables'])) {
            $old['deliverables'] = implode("\n", $old['deliverables']);
        }

        $this->render('admin/service-form', [
            'isNew'     => $service === null,
            'action'    => $service === null ? self::LIST : self::LIST . '/' . $id,
            'values'    => $this->values($defaults, $old),
            'errors'    => $errors,
            'deleteUrl' => $id > 0 ? self::LIST . '/' . $id . '/eliminar' : '',
            'back'      => self::LIST,
        ], $service === null ? 'Nuevo servicio' : 'Editar servicio');
    }

    /**
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'title'        => $this->postText('title'),
            'slug'         => $this->postText('slug'),
            'tagline'      => $this->postText('tagline'),
            'description'  => $this->postText('description'),
            'outcome'      => $this->postText('outcome'),
            'timeframe'    => $this->postText('timeframe'),
            'icon'         => $this->postText('icon'),
            'sort_order'   => $this->postInt('sort_order'),
            'is_featured'  => $this->postFlag('is_featured'),
            'is_published' => $this->postFlag('is_published'),
            'deliverables' => $this->postLines('deliverables'),
        ];
    }

    private function nextOrder(): int
    {
        $orders = array_map(
            static fn (array $s): int => (int) $s['sort_order'],
            (new Service())->adminList()
        );

        return $orders === [] ? 10 : max($orders) + 10;
    }
}
