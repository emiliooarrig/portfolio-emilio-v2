<?php
/**
 * Shell del modal de proyecto. Normalmente llega vacío y se llena con un
 * fetch a /proyectos/{slug}; si se entra directo a esa URL, el servidor ya
 * pinta el detalle aquí dentro (y sin JS el botón de cierre es un ancla).
 *
 * @var array<string, mixed>|null $project
 */

$project = $project ?? null;
?>
<div class="project-modal"
     id="project-modal"
     data-project-modal
     <?= $project ? 'data-modal-initial="open"' : 'hidden' ?>>

    <div class="project-modal__backdrop" data-modal-dismiss></div>

    <div class="project-modal__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="project-modal-title"
         aria-label="Detalle del proyecto"
         tabindex="-1"
         data-modal-dialog>

        <div class="project-modal__bar">
            <a class="cta cta--ghost cta--sm" href="<?= url('/') ?>#proyectos" data-modal-dismiss>Cerrar</a>
        </div>

        <div class="project-modal__body" data-modal-body>
            <?= $project ? partial('project-detail', ['project' => $project]) : '' ?>
        </div>
    </div>
</div>
