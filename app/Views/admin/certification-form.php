<?php
/**
 * Alta y edición de una certificación.
 *
 * @var bool                  $isNew
 * @var string                $action
 * @var array<string, mixed>  $values
 * @var array<string, string> $errors
 * @var string                $deleteUrl
 * @var string                $back
 * @var string                $token
 */
?>
<?= partial('admin-head', [
    'title' => $isNew ? 'Nueva certificación' : 'Editar certificación',
    'lead'  => 'Una credencial del grid. El título es un nombre propio: va tal cual lo emite la institución.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">La credencial</legend>

        <div class="admin-form__grid">
            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'title', 'label' => 'Título', 'required' => true,
                    'value' => $values['title'], 'error' => $errors['title'] ?? '',
                    'placeholder' => 'Professional Data Engineer',
                ]) ?>
            </div>

            <?= partial('admin-field', [
                'name' => 'issuer', 'label' => 'Emisor', 'required' => true,
                'value' => $values['issuer'], 'error' => $errors['issuer'] ?? '',
                'placeholder' => 'Google Cloud',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'credential_id', 'label' => 'ID de la credencial',
                'value' => $values['credential_id'], 'error' => $errors['credential_id'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'issued_on', 'label' => 'Emitida', 'type' => 'date', 'required' => true,
                'value' => $values['issued_on'], 'error' => $errors['issued_on'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'expires_on', 'label' => 'Vence', 'type' => 'date',
                'value' => $values['expires_on'], 'error' => $errors['expires_on'] ?? '',
                'hint'  => 'Vacío = no vence.',
            ]) ?>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'credential_url', 'label' => 'Enlace de verificación', 'type' => 'url',
                    'value' => $values['credential_url'], 'error' => $errors['credential_url'] ?? '',
                    'hint'  => 'La página donde se comprueba que es tuya.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'badge_image', 'label' => 'Insignia',
                    'value' => $values['badge_image'], 'error' => $errors['badge_image'] ?? '',
                    'hint'  => 'Ruta dentro de /public, por ejemplo /assets/img/badges/gcp.png',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'description', 'label' => 'Descripción', 'type' => 'textarea',
                    'value' => $values['description'], 'error' => $errors['description'] ?? '',
                    'rows'  => 3,
                ]) ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Publicación</legend>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'sort_order', 'label' => 'Orden', 'type' => 'number',
                'value' => $values['sort_order'], 'error' => $errors['sort_order'] ?? '',
            ]) ?>

            <div class="admin-form__flags">
                <?= partial('admin-field', [
                    'name' => 'is_published', 'label' => 'Publicada', 'type' => 'checkbox',
                    'value' => $values['is_published'],
                ]) ?>
            </div>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'     => $isNew ? 'Crear certificación' : 'Guardar cambios',
        'back'      => $back,
        'deleteUrl' => $deleteUrl,
    ]) ?>
</form>
