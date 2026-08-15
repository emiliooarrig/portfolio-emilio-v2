<?php
/**
 * Layout principal: header + nav, contenido, CTA de cierre y footer.
 *
 * @var array<string, mixed> $profile
 * @var string $content
 * @var string $activePath
 * @var string $pageTitle
 * @var string $pageDescription
 * @var int    $year
 */

$siteName = $profile['full_name'] . ' · ' . $profile['role_title'];
$title    = $pageTitle !== '' ? $pageTitle . ' — ' . $profile['full_name'] : $siteName;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($pageDescription !== '' ? $pageDescription : (string) $profile['headline']) ?>">
    <meta name="theme-color" content="#0F1620">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($pageDescription !== '' ? $pageDescription : (string) $profile['headline']) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="page" data-route="<?= e($activePath) ?>">

<a class="skip-link" href="#contenido">Saltar al contenido</a>

<?= partial('nav', ['activePath' => $activePath, 'profile' => $profile]) ?>

<main class="page__main" id="contenido">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__cta">
            <?= partial('cta-block', [
                'eyebrow'   => 'Siguiente paso',
                'title'     => '¿Tienes datos y ninguna respuesta clara?',
                'text'      => 'Cuéntame qué estás midiendo y en qué se atora. Respondo en menos de 24 horas.',
                'primary'   => ['label' => 'Contáctame', 'href' => '/contacto'],
                'secondary' => ['label' => 'Ver proyectos', 'href' => '/proyectos'],
                'variant'   => 'footer',
            ]) ?>
        </div>

        <div class="site-footer__meta">
            <div class="site-footer__identity">
                <span class="site-footer__name"><?= e($profile['full_name']) ?></span>
                <span class="mono site-footer__role"><?= e($profile['role_title']) ?></span>
            </div>

            <nav class="site-footer__links" aria-label="Enlaces de contacto">
                <?php if (! empty($profile['email'])): ?>
                    <a class="site-footer__link mono" href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                <?php endif; ?>
                <?php if (! empty($profile['github_url'])): ?>
                    <a class="site-footer__link mono" href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">GitHub</a>
                <?php endif; ?>
                <?php if (! empty($profile['linkedin_url'])): ?>
                    <a class="site-footer__link mono" href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn</a>
                <?php endif; ?>
            </nav>

            <p class="site-footer__copy mono">© <?= $year ?> · Construido en PHP, sin plantillas</p>
        </div>
    </div>
</footer>

<script type="module" src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
