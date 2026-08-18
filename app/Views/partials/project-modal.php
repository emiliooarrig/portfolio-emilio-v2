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
         aria-label="Detalle del proyecto"
         tabindex="-1"
         data-modal-dialog>

        <a class="project-modal__close" href="<?= url('/') ?>#proyectos" data-modal-dismiss aria-label="Cerrar detalle del proyecto">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"></path>
            </svg>
        </a>

        <div class="project-modal__body" data-modal-body>
            <?= $project ? partial('project-detail', ['project' => $project]) : '' ?>
        </div>
    </div>
</div>
