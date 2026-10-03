<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Upload;
use App\Models\Profile;

/**
 * Perfil: la fila única que alimenta el hero, «Sobre mí» y el contacto.
 *
 * No hay alta ni baja — el sitio es de una persona. Sólo se lee y se edita.
 */
class ProfileController extends AdminController
{
    private const SHOW = '/admin/perfil';

    public function index(): void
    {
        Auth::requireLogin();

        // Los datos ya viajan en `$profile`, compartido con todas las vistas.
        $this->render('admin/profile', [], 'Perfil');
    }

    public function edit(): void
    {
        Auth::requireLogin();

        [$errors, $old] = $this->takeFormState();

        $profile = (new Profile())->get();

        $defaults = [
            'full_name'          => $profile['full_name']    ?? '',
            'role_title'         => $profile['role_title']   ?? '',
            'headline'           => $profile['headline']     ?? '',
            'bio_short'          => $profile['bio_short']    ?? '',
            'bio_long'           => $profile['bio_long']     ?? '',
            'email'              => $profile['email']        ?? '',
            'phone'              => $profile['phone']        ?? '',
            'location'           => $profile['location']     ?? '',
            'avatar_path'        => $profile['avatar_path']  ?? '',
            'cv_path'            => $profile['cv_path']      ?? '',
            'github_url'         => $profile['github_url']   ?? '',
            'linkedin_url'       => $profile['linkedin_url'] ?? '',
            'website_url'        => $profile['website_url']  ?? '',
            'years_experience'   => (int) ($profile['years_experience'] ?? 0),
            'available_for_work' => (int) ($profile['available_for_work'] ?? 0),
        ];

        $this->render('admin/profile-form', [
            'action' => self::SHOW,
            'values' => $this->values($defaults, $old),
            'errors' => $errors,
            'back'   => self::SHOW,
            // Formatos y peso los decide la regla de subida, no la vista.
            'avatarRules' => Upload::image()->rules(),
            'cvRules'     => Upload::document()->rules(),
        ], 'Editar perfil');
    }

    public function update(): void
    {
        Auth::requireLogin();

        $this->guardUploadSize(self::SHOW . '/editar');
        $this->requireToken(self::SHOW . '/editar');

        $model   = new Profile();
        $current = $model->get();
        $files   = $this->files();
        $input   = $this->keepFiles($this->collect(), $files, $current);
        $errors  = $model->validate($input);

        if ($errors !== []) {
            $this->backWithErrors(self::SHOW . '/editar', $errors, $input);

            return;
        }

        $saved = $this->storeFiles($input, $files);

        if ($saved['errors'] !== []) {
            $this->backWithErrors(self::SHOW . '/editar', $saved['errors'], $input);

            return;
        }

        $model->update($saved['input']);
        $this->dropFiles($files, $saved['discarded']);

        $this->flash('success', 'Perfil guardado. El sitio ya lo muestra.');
        $this->redirect(self::SHOW);
    }

    /**
     * Los archivos del perfil, cada uno con su regla: dónde vive, qué
     * formatos admite y con qué nombre se guarda.
     *
     * @return array<string, array{field: string, upload: Upload, name: string}>
     */
    private function files(): array
    {
        $name = $this->postText('full_name');

        return [
            'avatar_path' => [
                'field'  => 'avatar',
                'upload' => Upload::image(),
                'name'   => 'foto-' . $name,
            ],
            'cv_path' => [
                'field'  => 'cv',
                'upload' => Upload::document(),
                'name'   => 'cv-' . $name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collect(): array
    {
        return [
            'full_name'          => $this->postText('full_name'),
            'role_title'         => $this->postText('role_title'),
            'headline'           => $this->postText('headline'),
            'bio_short'          => $this->postText('bio_short'),
            'bio_long'           => $this->postText('bio_long'),
            'email'              => $this->postText('email'),
            'phone'              => $this->postText('phone'),
            'location'           => $this->postText('location'),
            // `avatar_path` y `cv_path` no se teclean: los pone `update()` a
            // partir del archivo que se suba (o del que ya estaba).
            'github_url'         => $this->postText('github_url'),
            'linkedin_url'       => $this->postText('linkedin_url'),
            'website_url'        => $this->postText('website_url'),
            'years_experience'   => $this->postInt('years_experience'),
            'available_for_work' => $this->postFlag('available_for_work'),
        ];
    }
}
