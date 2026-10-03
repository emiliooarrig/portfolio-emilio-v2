<?php
/**
 * Alta y edición de una tecnología del stack.
 *
 * @var bool                  $isNew
 * @var string                $action
 * @var array<string, mixed>  $values
 * @var array<string, string> $errors
 * @var string                $deleteUrl
 * @var string                $back
 * @var string                $token
 */

use App\Models\Technology;
?>
<?= partial('admin-head', [
    'title' => $isNew ? 'Nueva tecnología' : 'Editar tecnología',
    'lead'  => 'Una línea del stack de «Sobre mí» y una tecnología disponible para los proyectos.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">La tecnología</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'name', 'label' => 'Nombre', 'required' => true,
                'value' => $values['name'], 'error' => $errors['name'] ?? '',
                'hint'  => 'Como se escribe de verdad: «PostgreSQL», no «postgres».',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'slug', 'label' => 'Clave',
                'value' => $values['slug'], 'error' => $errors['slug'] ?? '',
                'hint'  => 'Identificador interno. Si lo dejas vacío, sale del nombre.',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'category', 'label' => 'Categoría', 'type' => 'select',
                'value' => $values['category'], 'error' => $errors['category'] ?? '',
                'options' => Technology::CATEGORIES,
                'hint'    => 'Agrupa el stack en «Sobre mí»: Datos y Sistemas van primero.',
            ]) ?>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'      => $isNew ? 'Añadir al stack' : 'Guardar cambios',
        'back'       => $back,
        'deleteUrl'  => $deleteUrl,
        'deleteName' => (string) $values['name'],
        'deleteText' => 'Se borra del stack y de los proyectos que la usaban. No hay deshacer.',
    ]) ?>
</form>
