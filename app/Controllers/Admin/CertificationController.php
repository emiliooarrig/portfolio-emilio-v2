<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Certification;

/**
 * Certificaciones: el grid de credenciales.
 *
 * Los títulos son nombres propios y se guardan tal cual los emite cada
 * institución — aquí no se traducen ni se abrevian.
 */
class CertificationController extends AdminController
{
    private const LIST = '/admin/certificaciones';

    public function index(): void
    {
        Auth::requireLogin();

        $this->render('admin/certifications', [
            'certifications' => (new Certification())->adminList(),
        ], 'Certificaciones');
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->form(null);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();

        $cert = (new Certification())->find((int) $id);

        if ($cert === null) {
            $this->missing(self::LIST, 'Esa certificación');

            return;
        }

        $this->form($cert);
    }

    public function store(): void
    {
        $this->requireToken(self::LIST . '/nueva');

        $model  = new Certification();
        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/nueva', $errors, $input);

            return;
        }

        $model->create($input);

        $this->flash('success', 'Certificación «' . $input['title'] . '» creada.');
        $this->redirect(self::LIST);
    }

    public function update(string $id): void
    {
        $certId = (int) $id;

        $this->requireToken(self::LIST . '/' . $certId . '/editar');

        $model = new Certification();

        if ($model->find($certId) === null) {
            $this->missing(self::LIST, 'Esa certificación');

            return;
        }

        $input  = $this->collect();
        $errors = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::LIST . '/' . $certId . '/editar', $errors, $input);

            return;
        }

        $model->update($certId, $input);

        $this->flash('success', 'Certificación «' . $input['title'] . '» guardada.');
        $this->redirect(self::LIST);
    }

    public function confirmDelete(string $id): void
    {
        Auth::requireLogin();

        $cert = (new Certification())->find((int) $id);

        if ($cert === null) {
            $this->missing(self::LIST, 'Esa certificación');

            return;
        }

        $this->render('admin/confirm', [
            'title'  => 'Borrar «' . $cert['title'] . '»',
            'lead'   => 'Desaparece del grid de credenciales. No hay deshacer.',
            'detail' => [
                'Emisor'  => (string) $cert['issuer'],
                'Emitida' => month_year($cert['issued_on'], '—'),
            ],
            'action' => self::LIST . '/' . (int) $cert['id'] . '/eliminar',
            'back'   => self::LIST,
            'hint'   => 'Una credencial vencida no hace falta borrarla: sigue contando como formación.',
        ], 'Borrar certificación');
    }

    public function destroy(string $id): void
    {
        $certId = (int) $id;

        $this->requireToken(self::LIST);

        $model = new Certification();
        $cert  = $model->find($certId);

        if ($cert === null) {
            $this->missing(self::LIST, 'Esa certificación');

            return;
        }

        $model->delete($certId);

        $this->flash('success', 'Certificación «' . $cert['title'] . '» borrada.');
        $this->redirect(self::LIST);
    }

    /**
     * @param array<string, mixed>|null $cert
     */
    private function form(?array $cert): void
    {
        [$errors, $old] = $this->takeFormState();

        $id = $cert === null ? 0 : (int) $cert['id'];

        $defaults = [
            'title'          => $cert['title']          ?? '',
            'issuer'         => $cert['issuer']         ?? '',
            'credential_id'  => $cert['credential_id']  ?? '',
            'credential_url' => $cert['credential_url'] ?? '',
            'badge_image'    => $cert['badge_image']    ?? '',
            'description'    => $cert['description']    ?? '',
            'issued_on'      => $cert['issued_on']      ?? '',
            'expires_on'     => $cert['expires_on']     ?? '',
            'is_published'   => (int) ($cert['is_published'] ?? 1),
        ];

        $this->render('admin/certification-form', [
            'isNew'     => $cert === null,
            'action'    => $cert === null ? self::LIST : self::LIST . '/' . $id,
            'values'    => $this->values($defaults, $old),
            'errors'    => $errors,
            'deleteUrl' => $id > 0 ? self::LIST . '/' . $id . '/eliminar' : '',
            'back'      => self::LIST,
        ], $cert === null ? 'Nueva certificación' : 'Editar certificación');
    }

    /**
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'title'          => $this->postText('title'),
            'issuer'         => $this->postText('issuer'),
            'credential_id'  => $this->postText('credential_id'),
            'credential_url' => $this->postText('credential_url'),
            'badge_image'    => $this->postText('badge_image'),
            'description'    => $this->postText('description'),
            'issued_on'      => $this->postText('issued_on'),
            'expires_on'     => $this->postText('expires_on'),
            'is_published'   => $this->postFlag('is_published'),
        ];
    }
}
