<?php
/**
 * Contenido del detalle de proyecto. Se sirve como fragmento al modal
 * (fetch) o se inyecta en el shell cuando se entra directo a la URL.
 *
 * "Contexto" y "Resultado" van en lenguaje llano; "Qué hice" y el flujo
 * son donde sí toca el lenguaje técnico.
 *
 * @var array<string, mixed> $project
 */

$period = date_range($project['started_on'] ?? null, $project['ended_on'] ?? null);
$newTab = '<span class="visually-hidden"> (se abre en otra pestaña)</span>';
?>
<article class="project-detail">

    <header class="project-detail__head">
        <?php if ($period !== '' || ! empty($project['client'])): ?>
            <p class="project-detail__meta meta">
                <?php if ($period !== ''): ?><span><?= e($period) ?></span><?php endif; ?>
                <?php if (! empty($project['client'])): ?><span><?= e($project['client']) ?></span><?php endif; ?>
            </p>
        <?php endif; ?>

        <h2 class="project-detail__title" id="project-modal-title" data-detail-title><?= e($project['title']) ?></h2>

        <?php if (! empty($project['subtitle'])): ?>
            <p class="project-detail__subtitle"><?= e($project['subtitle']) ?></p>
        <?php endif; ?>

        <?php if (! empty($project['technologies'])): ?>
            <ul class="tech-list meta" aria-label="Tecnologías">
                <?php foreach ($project['technologies'] as $tech): ?>
                    <li><?= e($tech['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (! empty($project['repo_url']) || ! empty($project['demo_url'])): ?>
            <div class="project-detail__links">
                <?php if (! empty($project['repo_url'])): ?>
                    <a class="cta cta--secondary cta--sm" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener" data-cursor="Abrir"><?= roll_text('Repositorio') ?><?= $newTab ?></a>
                <?php endif; ?>
                <?php if (! empty($project['demo_url'])): ?>
                    <a class="cta cta--secondary cta--sm" href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener" data-cursor="Abrir"><?= roll_text('Demo') ?><?= $newTab ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>

    <div class="project-detail__cover">
        <?php if (! empty($project['cover_image'])): ?>
            <img src="<?= e(url((string) $project['cover_image'])) ?>" alt="" loading="lazy">
        <?php else: ?>
            <?php // Sin portada, la vista previa generada del proyecto: la misma
                  // que acompaña a su fila en el índice. ?>
            <?= partial('pattern', ['seed' => (string) $project['slug'], 'variant' => 'cover']) ?>
        <?php endif; ?>
    </div>

    <?php if (! empty($project['metrics'])): ?>
        <dl class="project-detail__metrics">
            <?php foreach (array_slice($project['metrics'], 0, 3) as $metric): ?>
                <?php // La etiqueta va primero en el marcado (dt antes que dd) y debajo
                      // en pantalla: el orden visual lo invierte el CSS. ?>
                <div class="project-detail__metric">
                    <dt class="meta"><?= e($metric['label']) ?></dt>
                    <dd class="project-detail__metric-value"><?= e($metric['value']) ?><?php if (! empty($metric['unit'])): ?><span class="project-detail__metric-unit"><?= e($metric['unit']) ?></span><?php endif; ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>

    <?php if (! empty($project['has_pipeline']) && ! empty($project['pipeline'])): ?>
        <section class="project-block">
            <h3 class="project-block__title">Cómo funciona</h3>
            <?= partial('steps', ['steps' => $project['pipeline'], 'variant' => 'compact']) ?>
        </section>
    <?php endif; ?>

    <?php if (! empty($project['context'])): ?>
        <section class="project-block">
            <h3 class="project-block__title">Contexto</h3>
            <div class="prose"><?= paragraphs($project['context']) ?></div>
        </section>
    <?php endif; ?>

    <?php if (! empty($project['solution'])): ?>
        <section class="project-block">
            <h3 class="project-block__title">Qué hice</h3>
            <div class="prose"><?= paragraphs($project['solution']) ?></div>
        </section>
    <?php endif; ?>

    <?php if (! empty($project['outcome'])): ?>
        <section class="project-block project-block--outcome">
            <h3 class="project-block__title">Resultado</h3>
            <div class="prose"><?= paragraphs($project['outcome']) ?></div>
        </section>
    <?php endif; ?>

    <?php if (! empty($project['role'])): ?>
        <p class="meta">Mi rol: <?= e($project['role']) ?></p>
    <?php endif; ?>

    <?php // Cierra el modal y lleva el scroll al formulario de contacto. ?>
    <footer class="project-detail__foot">
        <p>¿Quieres saber más de este proyecto?</p>
        <a class="cta cta--primary cta--sm" href="<?= url('/') ?>#contacto" data-modal-scroll="#contacto"><?= roll_text('Escríbeme') ?></a>
    </footer>
</article>
