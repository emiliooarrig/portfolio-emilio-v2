<?php
/**
 * Sección Proyectos destacados — grid bento; cada tarjeta abre el modal.
 *
 * @var array<int, array<string, mixed>> $projects
 * @var int $projectCount
 */
?>
<section class="section" id="proyectos">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Trabajo real',
            'title'   => 'Proyectos destacados',
            'lead'    => 'Casos que ya resolví: qué estaba fallando, qué hice y en qué mejoró. Toca cualquier tarjeta para leer la historia completa.',
            'meta'    => $projectCount . ' publicados',
        ]) ?>

        <?php if ($projects === []): ?>
            <p class="empty-state">Todavía no hay proyectos publicados.</p>
        <?php else: ?>
            <div class="bento bento--projects reveal--stagger">
                <?php foreach ($projects as $project): ?>
                    <?= partial('bento-card', ['project' => $project]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= partial('cta-block', [
            'title'     => '¿Lo tuyo se parece a alguno de estos?',
            'text'      => 'Cuéntame qué necesitas y te digo con franqueza si puedo ayudarte, antes de proponerte nada.',
            'primary'   => ['label' => 'Contáctame', 'href' => '#contacto', 'icon' => '→'],
            'secondary' => ['label' => 'Ver servicios', 'href' => '#servicios'],
            'variant'   => 'inline',
        ]) ?>
    </div>
</section>
