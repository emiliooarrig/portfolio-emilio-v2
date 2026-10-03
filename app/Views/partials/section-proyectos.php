<?php
/**
 * Sección Proyectos — índice en filas; cada fila abre el modal de detalle.
 *
 * Primero los destacados (ampliados, con su métrica principal) y después
 * el resto en una línea. Cada grupo conserva el orden por fechas que ya
 * devuelve `Project::published()`.
 *
 * @var array<int, array<string, mixed>> $projects
 */

$featured = array_filter($projects, static fn (array $p): bool => ! empty($p['is_featured']));
$others   = array_filter($projects, static fn (array $p): bool => empty($p['is_featured']));
?>
<section class="section" id="proyectos">
    <div class="container section__grid">
        <?= partial('section-header', [
            'title' => 'Proyectos',
            'lead'  => 'Qué estaba pasando, qué hice y qué cambió después. Abre cualquiera para ver el caso completo.',
        ]) ?>

        <div class="section__body">
            <?php if ($projects === []): ?>
                <p class="empty-state">Todavía no hay proyectos publicados.</p>
            <?php else: ?>
                <div class="row-list">
                    <?php foreach ($featured as $project): ?>
                        <?= partial('project-row', ['project' => $project, 'featured' => true]) ?>
                    <?php endforeach; ?>

                    <?php foreach ($others as $project): ?>
                        <?= partial('project-row', ['project' => $project, 'featured' => false]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
