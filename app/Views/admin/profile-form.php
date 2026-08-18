<?php
/**
 * Edición del perfil. No hay alta ni baja: es una sola fila.
 *
 * Lo que se toca aquí sale en el hero, en «Sobre mí» y en el pie, así que
 * cada campo dice dónde se va a ver.
 *
 * @var string                $action
 * @var array<string, mixed>  $values
 * @var array<string, string> $errors
 * @var string                $back
 * @var string                $token
 */
?>
<?= partial('admin-head', [
    'title' => 'Editar perfil',
    'lead'  => 'La fila única que alimenta el hero, «Sobre mí» y los datos de contacto.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Quién eres</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'full_name', 'label' => 'Nombre', 'required' => true,
                'value' => $values['full_name'], 'error' => $errors['full_name'] ?? '',
                'hint'  => 'El nombre gigante del inicio.',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'role_title', 'label' => 'Rol', 'required' => true,
                'value' => $values['role_title'], 'error' => $errors['role_title'] ?? '',
                'hint'  => 'La línea que va justo debajo del nombre.',
            ]) ?>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'headline', 'label' => 'Titular', 'required' => true,
                    'value' => $values['headline'], 'error' => $errors['headline'] ?? '',
                    'hint'  => 'Una frase: a qué te dedicas, en palabras de quien te contrata.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'bio_short', 'label' => 'Bio corta', 'type' => 'textarea',
                    'value' => $values['bio_short'], 'error' => $errors['bio_short'] ?? '',
                    'rows'  => 3,
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'bio_long', 'label' => 'Bio larga', 'type' => 'textarea',
                    'value' => $values['bio_long'], 'error' => $errors['bio_long'] ?? '',
                    'rows'  => 7,
                    'hint'  => 'La de «Sobre mí». Una línea en blanco separa párrafos.',
                ]) ?>
            </div>

            <?= partial('admin-field', [
                'name' => 'years_experience', 'label' => 'Años de experiencia', 'type' => 'number',
                'value' => $values['years_experience'], 'error' => $errors['years_experience'] ?? '',
                'hint'  => 'Sale en el contador animado.',
            ]) ?>

            <div class="admin-form__flags">
                <?= partial('admin-field', [
                    'name' => 'available_for_work', 'label' => 'Abierto a proyectos', 'type' => 'checkbox',
                    'value' => $values['available_for_work'],
                ]) ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Cómo te encuentran</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'email', 'label' => 'Correo', 'type' => 'email', 'required' => true,
                'value' => $values['email'], 'error' => $errors['email'] ?? '',
                'hint'  => 'Es el correo al que llega el formulario de contacto.',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'phone', 'label' => 'Teléfono',
                'value' => $values['phone'], 'error' => $errors['phone'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'location', 'label' => 'Ubicación',
                'value' => $values['location'], 'error' => $errors['location'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'github_url', 'label' => 'GitHub', 'type' => 'url',
                'value' => $values['github_url'], 'error' => $errors['github_url'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'linkedin_url', 'label' => 'LinkedIn', 'type' => 'url',
                'value' => $values['linkedin_url'], 'error' => $errors['linkedin_url'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'website_url', 'label' => 'Sitio web', 'type' => 'url',
                'value' => $values['website_url'], 'error' => $errors['website_url'] ?? '',
            ]) ?>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Archivos</legend>
        <p class="admin-form__note">Rutas dentro de <code>/public</code>. El panel no sube archivos: se copian a esa carpeta y aquí se apunta a ellos.</p>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'avatar_path', 'label' => 'Foto',
                'value' => $values['avatar_path'], 'error' => $errors['avatar_path'] ?? '',
                'placeholder' => '/assets/img/emilio.jpg',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'cv_path', 'label' => 'CV',
                'value' => $values['cv_path'], 'error' => $errors['cv_path'] ?? '',
                'placeholder' => '/assets/docs/cv-emilio-guzman.pdf',
            ]) ?>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'     => 'Guardar perfil',
        'back'      => $back,
        'deleteUrl' => '',
    ]) ?>
</form>
