<?php
/**
 * Sección Cómo trabajo — el método, en cinco pasos.
 *
 * Los textos viven aquí, como los leads de las demás secciones: no son
 * contenido que se edite desde el panel, son la forma de trabajar.
 */

$steps = [
    ['label' => 'Entender',        'description' => 'Antes de tocar nada, pregunto cómo se usa hoy y a quién le afecta.'],
    ['label' => 'Diagnosticar',    'description' => 'Busco la causa con datos y registros, no con suposiciones.'],
    ['label' => 'Resolver',        'description' => 'Aplico el cambio más simple que lo arregla de verdad, y lo pruebo.'],
    ['label' => 'Documentar',      'description' => 'Dejo escrito qué cambió y por qué, para que nadie dependa de mí.'],
    ['label' => 'Dar seguimiento', 'description' => 'Vuelvo a medir después para confirmar que sigue funcionando.'],
];
?>
<section class="section" id="como-trabajo">
    <div class="container section__grid">
        <?= partial('section-header', [
            'title' => 'Cómo trabajo',
            'lead'  => 'Sea un servidor, una base de datos o una aplicación, el método es el mismo.',
        ]) ?>

        <div class="section__body">
            <?= partial('steps', ['steps' => $steps, 'variant' => 'section']) ?>
        </div>
    </div>
</section>
