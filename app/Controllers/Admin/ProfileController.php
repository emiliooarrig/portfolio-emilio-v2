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

        // Un POST que se pasa de `post_max_size` llega vacío: sin token y sin
        // campos. Sin este aviso el panel diría que caducó la sesión, cuando
        // el problema es el archivo.
        if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->flash('error', 'El envío pesa más de lo que admite el servidor. Sube un archivo más ligero.');
            $this->redirect(self::SHOW . '/editar');

            return;
        }

        $this->requireToken(self::SHOW . '/editar');

        $model   = new Profile();
        $current = $model->get();
        $input   = $this->collect();
        $errors  = $model->validate($input);

        // Cada archivo con su regla: dónde vive, qué formatos admite y con qué
        // nombre se guarda. La columna conserva lo que ya había mientras nadie
        // suba nada ni pida quitarlo.
        $files = [
            'avatar_path' => [
                'field'  => 'avatar',
                'upload' => Upload::image(),
                'name'   => 'foto-' . $input['full_name'],
            ],
            'cv_path' => [
                'field'  => 'cv',
                'upload' => Upload::document(),
                'name'   => 'cv-' . $input['full_name'],
            ],
        ];

        foreach (array_keys($files) as $column) {
            $input[$column] = (string) ($current[$column] ?? '');
        }

        if ($errors !== []) {
            $this->backWithErrors(self::SHOW . '/editar', $errors, $input);

            return;
        }

        // Los archivos se guardan sólo cuando el resto del formulario ya está
        // bien: así no queda nada suelto en /public sin fila que lo apunte.
        $uploaded = [];

        foreach ($files as $column => $file) {
            if (! $file['upload']->received($file['field'])) {
                continue;
            }

            $path = $file['upload']->store($file['field'], $file['name']);

            if ($path === null) {
                $errors[$file['field']] = $file['upload']->error();

                continue;
            }

            $uploaded[$column] = $path;
        }

        if ($errors !== []) {
            $this->backWithErrors(self::SHOW . '/editar', $errors, $input);

            return;
        }

        // Lo que deja de usarse: la ruta anterior de cada columna que cambia,
        // sea porque llegó un archivo nuevo o porque se pidió quitarlo.
        $discarded = [];

        foreach ($files as $column => $file) {
            $remove = $this->postFlag('remove_' . $file['field']) === 1;

            if (! isset($uploaded[$column]) && ! $remove) {
                continue;
            }

            $discarded[$column] = $input[$column];
            $input[$column]     = $uploaded[$column] ?? '';
        }

        $model->update($input);

        // El borrado va después de guardar: si el UPDATE fallara, el archivo
        // viejo sigue en su sitio y la fila lo sigue apuntando.
        foreach ($discarded as $column => $previous) {
            $files[$column]['upload']->remove($previous);
        }

        $this->flash('success', 'Perfil guardado. El sitio ya lo muestra.');
        $this->redirect(self::SHOW);
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
