<?php
/**
 * Proyectos — grid bento.
 *
 * @var array<int, array<string, mixed>> $projects
 */
?>
<section class="section">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Trabajo',
            'title'   => 'Proyectos destacados',
            'lead'    => 'Plataformas de datos, pipelines y capas analíticas. Cada tarjeta abre el detalle: contexto, decisiones técnicas y resultado medible.',
            'meta'    => count($projects) . ' proyectos',
        ]) ?>

        <?php if ($projects === []): ?>
            <p class="empty-state">Todavía no hay proyectos publicados.</p>
        <?php else: ?>
            <div class="bento bento--projects">
                <?php foreach ($projects as $project): ?>
                    <?= partial('bento-card', ['project' => $project]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= partial('cta-block', [
            'title'     => '¿Buscas a alguien para tu proyecto de datos? Hablemos.',
            'text'      => 'Diagnóstico honesto de tu arquitectura actual antes de proponer nada.',
            'primary'   => ['label' => 'Contáctame', 'href' => '/contacto'],
            'secondary' => ['label' => 'Ver experiencia', 'href' => '/experiencia'],
            'variant'   => 'inline',
        ]) ?>
    </div>
</section>
