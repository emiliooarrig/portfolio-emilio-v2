<?php
/**
 * Layout principal de la landing: nav sticky, scroll único con secciones
 * ancladas, footer con CTA de cierre y el shell del modal de proyecto.
 *
 * @var array<string, mixed> $profile
 * @var string $content
 * @var string $activePath
 * @var string $pageTitle
 * @var string $pageDescription
 * @var int    $year
 * @var array<string, mixed>|null $openProject
 */

$openProject = $openProject ?? null;
$landing     = $landing ?? false;
$siteName    = $profile['full_name'] . ' · ' . $profile['role_title'];

// Fuera de la landing (404) las anclas necesitan volver a la raíz primero.
$anchorPrefix = $landing ? '' : url('/');

// Al entrar directo a /proyectos/{slug} el <title> habla del proyecto.
$title = $openProject
    ? $openProject['title'] . ' — ' . $profile['full_name']
    : ($pageTitle !== '' ? $pageTitle . ' — ' . $profile['full_name'] : $siteName);

$description = $openProject
    ? excerpt((string) $openProject['summary'], 155)
    : ($pageDescription !== '' ? $pageDescription : (string) $profile['headline']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#0A0E15">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">

    <?php // Marca `js` antes de pintar: las celdas con scroll-reveal nacen ocultas sólo si hay JS. ?>
    <script>document.documentElement.classList.add('js');</script>

    <?php
    // Sekuya y Stack Sans Headline se sirven desde el propio dominio: sin
    // Google Fonts. Se precargan porque el CSS las descubre tarde y el hero
    // es lo primero que se pinta. Mientras los .woff2 no estén en
    // public/assets/fonts/ estos dos preloads dan 404 y la página cae en el
    // respaldo del sistema (ver el README de esa carpeta).
    ?>
    <?php // Sin ?v=: la URL debe ser idéntica a la que pide main.css o se descarga dos veces. ?>
    <link rel="preload" href="<?= url('/assets/fonts/stack-sans-headline.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= url('/assets/fonts/sekuya.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="page"
      data-route="<?= e($activePath) ?>"
      data-base="<?= e(rtrim((string) config('app.base_path', ''), '/')) ?>"
      data-site-title="<?= e($siteName) ?>">

<a class="skip-link" href="<?= e($anchorPrefix) ?>#inicio">Saltar al contenido</a>

<?= partial('nav', ['profile' => $profile, 'anchorPrefix' => $anchorPrefix]) ?>

<main class="page__main" id="contenido">
    <?= $content ?>
</main>

<footer class="site-footer" data-spy-end>
    <div class="container">
        <div class="site-footer__cta">
            <?= partial('cta-block', [
                'eyebrow'   => 'Siguiente paso',
                'title'     => '¿Tienes una idea o algo que no acaba de funcionar?',
                'text'      => 'Cuéntamelo en tus palabras. Si puedo ayudarte te lo digo, y si no, también.',
                'primary'   => ['label' => 'Contáctame', 'href' => $anchorPrefix . '#contacto', 'icon' => '→'],
                'secondary' => ['label' => 'Ver servicios', 'href' => $anchorPrefix . '#servicios'],
                'variant'   => 'footer',
            ]) ?>
        </div>

        <div class="site-footer__meta">
            <div class="site-footer__identity">
                <span class="site-footer__name"><?= e($profile['full_name']) ?></span>
                <span class="meta site-footer__role"><?= e($profile['role_title']) ?></span>
            </div>

            <nav class="site-footer__links" aria-label="Enlaces de contacto">
                <?php if (! empty($profile['email'])): ?>
                    <a class="site-footer__link meta" href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                <?php endif; ?>
                <?php if (! empty($profile['github_url'])): ?>
                    <a class="site-footer__link meta" href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">GitHub</a>
                <?php endif; ?>
                <?php if (! empty($profile['linkedin_url'])): ?>
                    <a class="site-footer__link meta" href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a>
                <?php endif; ?>
            </nav>

            <p class="site-footer__copy meta">© <?= $year ?> · Construido en PHP, sin plantillas</p>
        </div>
    </div>
</footer>

<a class="to-top" href="<?= e($anchorPrefix) ?>#inicio" data-to-top aria-label="Volver al inicio">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
        <path d="M12 19V5M6 11l6-6 6 6"></path>
    </svg>
</a>

<?= partial('project-modal', ['project' => $openProject]) ?>

<script type="module" src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
