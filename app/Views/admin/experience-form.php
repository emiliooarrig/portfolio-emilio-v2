<?php
/**
 * Alta y edición de un puesto del timeline.
 *
 * @var bool                  $isNew
 * @var string                $action
 * @var array<string, mixed>  $values
 * @var array<string, string> $errors
 * @var string                $deleteUrl
 * @var string                $back
 * @var string                $token
 */

use App\Models\Experience;
?>
<?= partial('admin-head', [
    'title' => $isNew ? 'Nuevo puesto' : 'Editar puesto',
    'lead'  => 'Un puesto del timeline. Aquí el lenguaje técnico se queda: lo lee quien lo entiende.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">El puesto</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'role', 'label' => 'Puesto', 'required' => true,
                'value' => $values['role'], 'error' => $errors['role'] ?? '',
                'placeholder' => 'Data Engineer Senior',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'company', 'label' => 'Empresa', 'required' => true,
                'value' => $values['company'], 'error' => $errors['company'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'location', 'label' => 'Ubicación',
                'value' => $values['location'], 'error' => $errors['location'] ?? '',
                'placeholder' => 'Guadalajara, MX',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'employment_type', 'label' => 'Contratación', 'type' => 'select',
                'value' => $values['employment_type'], 'error' => $errors['employment_type'] ?? '',
                'options' => Experience::TYPES,
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'started_on', 'label' => 'Inicio', 'type' => 'date', 'required' => true,
                'value' => $values['started_on'], 'error' => $errors['started_on'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'ended_on', 'label' => 'Fin', 'type' => 'date',
                'value' => $values['ended_on'], 'error' => $errors['ended_on'] ?? '',
                'hint'  => 'Vacío = sigue en marcha. Es lo único que marca el puesto actual, y lo que pone «Actual» en el timeline.',
            ]) ?>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'company_url', 'label' => 'Sitio de la empresa', 'type' => 'url',
                    'value' => $values['company_url'], 'error' => $errors['company_url'] ?? '',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'summary', 'label' => 'Resumen', 'type' => 'textarea',
                    'value' => $values['summary'], 'error' => $errors['summary'] ?? '',
                    'rows'  => 3,
                    'hint'  => 'De qué iba el puesto. Una línea en blanco separa párrafos.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'highlights', 'label' => 'Logros', 'type' => 'textarea',
                    'value' => $values['highlights'], 'error' => $errors['highlights'] ?? '',
                    'rows'  => 5,
                    'hint'  => 'Uno por línea, en el orden en que quieres leerlos.',
                ]) ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Publicación</legend>

        <div class="admin-form__grid">
            <div class="admin-form__flags">
                <?= partial('admin-field', [
                    'name' => 'is_published', 'label' => 'Publicada', 'type' => 'checkbox',
                    'value' => $values['is_published'],
                ]) ?>
            </div>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'      => $isNew ? 'Crear puesto' : 'Guardar cambios',
        'back'       => $back,
        'deleteUrl'  => $deleteUrl,
        'deleteName' => (string) $values['role'],
        'deleteText' => 'Se borra el puesto y con él sus logros. Desaparece del timeline. No hay deshacer.',
    ]) ?>
</form>
