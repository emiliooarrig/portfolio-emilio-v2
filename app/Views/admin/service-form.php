<?php
/**
 * Alta y edición de un servicio.
 *
 * @var bool                  $isNew
 * @var string                $action
 * @var array<string, mixed>  $values
 * @var array<string, string> $errors
 * @var string                $deleteUrl
 * @var string                $back
 * @var string                $token
 */

use App\Models\Service;
?>
<?= partial('admin-head', [
    'title' => $isNew ? 'Nuevo servicio' : 'Editar servicio',
    'lead'  => 'Una tarjeta del carrusel. Lo lee un cliente que no es de sistemas: sin siglas y sin nombres de herramientas.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">La tarjeta</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'title', 'label' => 'Nombre', 'required' => true,
                'value' => $values['title'], 'error' => $errors['title'] ?? '',
                'hint'  => 'Cómo lo llamaría quien te contrata, no cómo lo llamas tú.',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'slug', 'label' => 'Clave',
                'value' => $values['slug'], 'error' => $errors['slug'] ?? '',
                'hint'  => 'Identificador interno. Si lo dejas vacío, sale del nombre.',
            ]) ?>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'tagline', 'label' => 'Gancho',
                    'value' => $values['tagline'], 'error' => $errors['tagline'] ?? '',
                    'hint'  => 'Una frase corta que se lea de un vistazo.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'description', 'label' => 'Qué haces', 'type' => 'textarea', 'required' => true,
                    'value' => $values['description'], 'error' => $errors['description'] ?? '',
                    'rows'  => 4,
                    'hint'  => 'Máximo 500 caracteres, en segunda persona y sin jerga.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'deliverables', 'label' => 'Entregables', 'type' => 'textarea',
                    'value' => $values['deliverables'], 'error' => $errors['deliverables'] ?? '',
                    'rows'  => 4,
                    'hint'  => 'Uno por línea, dos a cuatro. Cada uno corto: son viñetas, no párrafos.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'outcome', 'label' => 'Para qué le sirve',
                    'value' => $values['outcome'], 'error' => $errors['outcome'] ?? '',
                ]) ?>
            </div>

            <?= partial('admin-field', [
                'name' => 'timeframe', 'label' => 'Plazo típico',
                'value' => $values['timeframe'], 'error' => $errors['timeframe'] ?? '',
                'placeholder' => '2 a 4 semanas',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'icon', 'label' => 'Icono', 'type' => 'select',
                'value' => $values['icon'], 'error' => $errors['icon'] ?? '',
                'options' => Service::ICONS,
            ]) ?>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Publicación</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'sort_order', 'label' => 'Orden', 'type' => 'number',
                'value' => $values['sort_order'], 'error' => $errors['sort_order'] ?? '',
                'hint'  => 'Es el orden en que se recorre el carrusel.',
            ]) ?>

            <div class="admin-form__flags">
                <?= partial('admin-field', [
                    'name' => 'is_published', 'label' => 'Publicado', 'type' => 'checkbox',
                    'value' => $values['is_published'],
                ]) ?>

                <?= partial('admin-field', [
                    'name' => 'is_featured', 'label' => 'Destacado', 'type' => 'checkbox',
                    'value' => $values['is_featured'],
                ]) ?>
            </div>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'     => $isNew ? 'Crear servicio' : 'Guardar cambios',
        'back'      => $back,
        'deleteUrl' => $deleteUrl,
    ]) ?>
</form>
