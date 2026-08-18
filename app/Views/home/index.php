<?php
/**
 * Única vista del sitio: las secciones se apilan en el orden del scroll.
 * Inicio → Sobre mí → Proyectos → Servicios → Certificaciones → Experiencia → Contacto.
 *
 * @var array<string, mixed> $profile
 * @var array<int, array<string, mixed>> $projects
 * @var array<int, array<string, mixed>> $services
 * @var array<string, array<int, array<string, mixed>>> $stackByCategory
 * @var array<int, array<string, mixed>> $experiences
 * @var array<int, array<string, mixed>> $certifications
 * @var array<int, string> $issuers
 * @var array<string, mixed>|null $currentRole
 * @var int|null $careerStart
 * @var int $projectCount
 * @var array<string, string> $contactErrors
 * @var array<string, string> $contactOld
 * @var array{type: string, text: string}|null $flash
 */
?>
<?= partial('section-inicio', ['profile' => $profile]) ?>

<?= partial('section-sobre-mi', [
    'profile'         => $profile,
    'currentRole'     => $currentRole,
    'careerStart'     => $careerStart,
    'stackByCategory' => $stackByCategory,
]) ?>

<?= partial('section-proyectos', [
    'projects'     => $projects,
    'projectCount' => $projectCount,
]) ?>

<?= partial('section-servicios', ['services' => $services]) ?>

<?= partial('section-certificaciones', [
    'certifications' => $certifications,
    'issuers'        => $issuers,
]) ?>

<?= partial('section-experiencia', [
    'experiences' => $experiences,
    'careerStart' => $careerStart,
    'profile'     => $profile,
]) ?>

<?= partial('section-contacto', [
    'profile' => $profile,
    'errors'  => $contactErrors,
    'old'     => $contactOld,
    'flash'   => $flash,
]) ?>
