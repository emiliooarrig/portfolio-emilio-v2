<?php
/**
 * Alta y edición de un proyecto.
 *
 * Todo va en un solo envío: los datos, el stack, las métricas y el flujo. No
 * hay "guarda primero y luego añade lo demás", que es donde se quedan a medias
 * las fichas.
 *
 * @var bool                                 $isNew
 * @var string                               $action
 * @var array<string, mixed>                 $values
 * @var array<string, string>                $errors
 * @var array<int, array<string, mixed>>     $technologies  todo el stack disponible
 * @var array<int, int>                      $selected      ids marcados
 * @var array<int, array<string, mixed>>     $metrics
 * @var array<int, array<string, mixed>>     $pipeline
 * @var array<string, string>                $coverRules    formatos y peso de la portada
 * @var string                               $deleteUrl
 * @var string                               $back
 * @var string                               $token
 */

use App\Models\Project;
use App\Models\Technology;

$selected = array_map('intval', $selected);

/** Filas existentes + tres huecos: se añaden tres por guardado. */
$blank = static fn (array $columns): array => array_fill_keys($columns, '');

$metricSlots   = array_merge($metrics, array_fill(0, 3, $blank(['label', 'value', 'unit'])));
$pipelineSlots = array_merge($pipeline, array_fill(0, 3, $blank(['label', 'description', 'stage'])));

$grouped = [];
foreach ($technologies as $technology) {
    $grouped[(string) $technology['category']][] = $technology;
}
?>
<?= partial('admin-head', [
    'title' => $isNew ? 'Nuevo proyecto' : 'Editar proyecto',
    'lead'  => 'La fila del índice de proyectos y el modal de detalle salen de aquí.',
]) ?>

<form class="admin-form" method="post" action="<?= url($action) ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="_token" value="<?= e($token) ?>">

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">La fila</legend>
        <p class="admin-form__note">Esto es lo que se ve sin abrir nada. Va en lenguaje de cliente: el resultado antes que el método.</p>

        <div class="admin-form__grid">
            <?= partial('admin-field', [
                'name' => 'title', 'label' => 'Título', 'required' => true,
                'value' => $values['title'], 'error' => $errors['title'] ?? '',
                'hint'  => 'Lo que consiguió el proyecto, no cómo se hizo.',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'slug', 'label' => 'URL',
                'value' => $values['slug'], 'error' => $errors['slug'] ?? '',
                'hint'  => 'Se queda en /proyectos/… Si lo dejas vacío, sale del título.',
                'placeholder' => 'plataforma-datos-retail',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'subtitle', 'label' => 'Subtítulo',
                'value' => $values['subtitle'], 'error' => $errors['subtitle'] ?? '',
            ]) ?>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'summary', 'label' => 'Resumen', 'type' => 'textarea', 'required' => true,
                    'value' => $values['summary'], 'error' => $errors['summary'] ?? '',
                    'rows'  => 3,
                    'hint'  => 'Máximo 400 caracteres: es la descripción que se ve al compartir el enlace del proyecto.',
                ]) ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">El detalle</legend>
        <p class="admin-form__note">Lo que se lee dentro del modal. «Contexto» y «Resultado» van en lenguaje llano; «Qué hice» es donde sí toca el lenguaje técnico.</p>

        <div class="admin-form__grid">
            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'context', 'label' => 'Contexto', 'type' => 'textarea',
                    'value' => $values['context'], 'error' => $errors['context'] ?? '',
                    'hint'  => 'Qué estaba pasando antes. Una línea en blanco separa párrafos.',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'solution', 'label' => 'Qué hice', 'type' => 'textarea',
                    'value' => $values['solution'], 'error' => $errors['solution'] ?? '',
                ]) ?>
            </div>

            <div class="admin-form__wide">
                <?= partial('admin-field', [
                    'name' => 'outcome', 'label' => 'Resultado', 'type' => 'textarea',
                    'value' => $values['outcome'], 'error' => $errors['outcome'] ?? '',
                    'hint'  => 'En qué cambió el día a día de quien lo usa.',
                ]) ?>
            </div>

            <?= partial('admin-field', [
                'name' => 'role', 'label' => 'Mi papel',
                'value' => $values['role'], 'error' => $errors['role'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'client', 'label' => 'Cliente',
                'value' => $values['client'], 'error' => $errors['client'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'started_on', 'label' => 'Inicio', 'type' => 'date',
                'value' => $values['started_on'], 'error' => $errors['started_on'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'ended_on', 'label' => 'Fin', 'type' => 'date',
                'value' => $values['ended_on'], 'error' => $errors['ended_on'] ?? '',
                'hint'  => 'Vacío = sigue en marcha.',
            ]) ?>

            <?= partial('admin-file', [
                'name'    => 'cover_image', 'label' => 'Imagen de portada',
                'current' => $values['cover_image'], 'error' => $errors['cover_image'] ?? '',
                'accept'  => $coverRules['accept'], 'formats' => $coverRules['formats'],
                'limit'   => $coverRules['limit'],
                'preview' => 'image', 'remove' => 'remove_cover_image',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'repo_url', 'label' => 'Repositorio', 'type' => 'url',
                'value' => $values['repo_url'], 'error' => $errors['repo_url'] ?? '',
            ]) ?>

            <?= partial('admin-field', [
                'name' => 'demo_url', 'label' => 'Demo', 'type' => 'url',
                'value' => $values['demo_url'], 'error' => $errors['demo_url'] ?? '',
            ]) ?>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Stack</legend>
        <p class="admin-form__note">Las tecnologías de la fila y del detalle. Si falta alguna, se da de alta en <a href="<?= url('/admin/tecnologias') ?>">Stack</a>.</p>

        <?php if ($technologies === []): ?>
            <p class="admin-empty">No hay tecnologías dadas de alta todavía.</p>
        <?php else: ?>
            <div class="admin-picker">
                <?php foreach ($grouped as $category => $items): ?>
                    <div class="admin-picker__group">
                        <p class="admin-picker__label meta"><?= e(Technology::categoryLabel($category)) ?></p>

                        <?php foreach ($items as $technology): ?>
                            <label class="admin-check">
                                <input class="admin-check__box"
                                       type="checkbox"
                                       name="technologies[]"
                                       value="<?= (int) $technology['id'] ?>"
                                       <?= in_array((int) $technology['id'], $selected, true) ? 'checked' : '' ?>>
                                <span class="admin-check__label"><?= e($technology['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Métricas</legend>
        <p class="admin-form__note">Los números duros del detalle. Vacía la etiqueta para quitar una fila; se guardan en este orden.</p>

        <?php if (isset($errors['metrics'])): ?>
            <p class="form__error" role="alert"><?= e($errors['metrics']) ?></p>
        <?php endif; ?>

        <div class="admin-rows">
            <div class="admin-rows__head meta">
                <span>Etiqueta</span><span>Valor</span><span>Unidad</span>
            </div>

            <?php foreach ($metricSlots as $index => $metric): ?>
                <div class="admin-rows__row">
                    <input class="form__input" type="text"
                           name="metrics[<?= $index ?>][label]"
                           value="<?= e((string) ($metric['label'] ?? '')) ?>"
                           aria-label="Etiqueta de la métrica <?= $index + 1 ?>"
                           placeholder="Tiempo del reporte">
                    <input class="form__input" type="text"
                           name="metrics[<?= $index ?>][value]"
                           value="<?= e((string) ($metric['value'] ?? '')) ?>"
                           aria-label="Valor de la métrica <?= $index + 1 ?>"
                           placeholder="15">
                    <input class="form__input" type="text"
                           name="metrics[<?= $index ?>][unit]"
                           value="<?= e((string) ($metric['unit'] ?? '')) ?>"
                           aria-label="Unidad de la métrica <?= $index + 1 ?>"
                           placeholder="min">
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Flujo de datos</legend>
        <p class="admin-form__note">Los pasos de «Cómo funciona» dentro del detalle. Sólo se muestran si marcas «Mostrar el flujo» más abajo.</p>

        <?php if (isset($errors['pipeline'])): ?>
            <p class="form__error" role="alert"><?= e($errors['pipeline']) ?></p>
        <?php endif; ?>

        <div class="admin-rows admin-rows--pipeline">
            <div class="admin-rows__head meta">
                <span>Paso</span><span>Descripción</span><span>Etapa</span>
            </div>

            <?php foreach ($pipelineSlots as $index => $step): ?>
                <div class="admin-rows__row">
                    <input class="form__input" type="text"
                           name="pipeline[<?= $index ?>][label]"
                           value="<?= e((string) ($step['label'] ?? '')) ?>"
                           aria-label="Nombre del paso <?= $index + 1 ?>"
                           placeholder="Ingesta">
                    <input class="form__input" type="text"
                           name="pipeline[<?= $index ?>][description]"
                           value="<?= e((string) ($step['description'] ?? '')) ?>"
                           aria-label="Descripción del paso <?= $index + 1 ?>">
                    <select class="form__input form__input--select"
                            name="pipeline[<?= $index ?>][stage]"
                            aria-label="Etapa del paso <?= $index + 1 ?>">
                        <?php foreach (Project::STAGES as $stage => $stageLabel): ?>
                            <option value="<?= e($stage) ?>"
                                <?php // Los huecos vacíos arrancan en "dato crudo", que es por donde empieza un flujo. ?>
                                <?= (string) ($step['stage'] ?: 'raw') === $stage ? 'selected' : '' ?>>
                                <?= e($stageLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <fieldset class="admin-form__group">
        <legend class="admin-form__legend">Publicación</legend>

        <div class="admin-form__grid">
            <div class="admin-form__flags">
                <?= partial('admin-field', [
                    'name' => 'is_published', 'label' => 'Publicado', 'type' => 'checkbox',
                    'value' => $values['is_published'],
                    'hint'  => 'Sin marcar sigue aquí, pero no en el sitio.',
                ]) ?>

                <?= partial('admin-field', [
                    'name' => 'is_featured', 'label' => 'Destacado', 'type' => 'checkbox',
                    'value' => $values['is_featured'],
                    'hint'  => 'Sale primero, ampliado y con su primera métrica en grande.',
                ]) ?>

                <?= partial('admin-field', [
                    'name' => 'has_pipeline', 'label' => 'Mostrar el flujo', 'type' => 'checkbox',
                    'value' => $values['has_pipeline'],
                    'hint'  => 'Muestra los pasos numerados dentro del modal.',
                ]) ?>
            </div>
        </div>
    </fieldset>

    <?= partial('admin-form-actions', [
        'label'      => $isNew ? 'Crear proyecto' : 'Guardar cambios',
        'back'       => $back,
        'deleteUrl'  => $deleteUrl,
        'deleteName' => (string) $values['title'],
        'deleteText' => 'Se borra el proyecto y con él su stack, sus métricas y su flujo. No hay deshacer.',
    ]) ?>
</form>
