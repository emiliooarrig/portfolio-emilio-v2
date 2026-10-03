<?php
/**
 * Única vista del sitio: las secciones se apilan en el orden del scroll.
 * Inicio → Proyectos → Cómo trabajo → Experiencia → Sobre mí → Certificaciones → Contacto.
 *
 * La prueba (proyectos) va primero porque es lo que un reclutador busca
 * antes que nada; "Sobre mí" baja porque sólo interesa una vez que el
 * trabajo convenció.
 *
 * @var array<string, mixed> $profile
 * @var array<int, array<string, mixed>> $projects
 * @var array<string, array<int, array<string, mixed>>> $stackByCategory
 * @var array<int, array<string, mixed>> $experiences
 * @var array<int, array<string, mixed>> $certifications
 * @var array<string, mixed>|null $currentRole
 * @var array<string, string> $contactErrors
 * @var array<string, string> $contactOld
 * @var array{type: string, text: string}|null $flash  lo pinta el layout, como alerta
 */
?>
<?= partial('section-inicio', ['profile' => $profile]) ?>

<?= partial('section-proyectos', ['projects' => $projects]) ?>

<?= partial('section-metodo') ?>

<?= partial('section-experiencia', ['experiences' => $experiences]) ?>

<?= partial('section-sobre-mi', [
    'profile'         => $profile,
    'currentRole'     => $currentRole,
    'stackByCategory' => $stackByCategory,
]) ?>

<?= partial('section-certificaciones', ['certifications' => $certifications]) ?>

<?= partial('section-contacto', [
    'profile' => $profile,
    'errors'  => $contactErrors,
    'old'     => $contactOld,
]) ?>
