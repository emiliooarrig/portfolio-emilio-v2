<?php
/**
 * Layout principal de la landing: nav fijo, scroll único con secciones
 * ancladas, footer de una fila y el shell del modal de proyecto.
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
$siteName    = $profile['full_name'] . ', ' . $profile['role_title'];

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
    <?php // Tema antes de pintar nada: claro salvo que se haya elegido oscuro
          // con el botón (no sigue al sistema). Sin esto, recargar en oscuro
          // destella en claro. El mismo script marca `js`: los estados ocultos
          // (nombre del hero, apariciones) sólo existen si hay JS que los revele. ?>
    <meta name="theme-color" content="#F5F6F8">
    <script>(function(){var t='light';try{if(localStorage.getItem('theme')==='dark')t='dark'}catch(e){}var d=document.documentElement;d.dataset.theme=t;if(t==='dark')document.querySelector('meta[name="theme-color"]').content='#0E1014';d.classList.add('js');})();</script>

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">

    <?php // Mona Sans se sirve desde el propio dominio y se precarga: el CSS la
          // descubre tarde y el nombre del hero es lo primero que se pinta.
          // Sin ?v=: la URL debe ser idéntica a la que pide main.css o se descarga dos veces. ?>
    <link rel="preload" href="<?= url('/assets/fonts/hubot-sans.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= url('/assets/fonts/mona-sans.woff2') ?>" as="font" type="font/woff2" crossorigin>
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
        <div class="site-footer__inner">
            <p class="site-footer__identity">
                <span class="site-footer__name"><?= e($profile['full_name']) ?></span>,
                <span class="site-footer__role"><?= e($profile['role_title']) ?></span>
            </p>

            <nav class="site-footer__links" aria-label="Enlaces de contacto">
                <?php if (! empty($profile['email'])): ?>
                    <a class="site-footer__link meta" href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a>
                <?php endif; ?>
                <?php if (! empty($profile['github_url'])): ?>
                    <a class="site-footer__link meta" href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">GitHub<span class="visually-hidden"> (se abre en otra pestaña)</span></a>
                <?php endif; ?>
                <?php if (! empty($profile['linkedin_url'])): ?>
                    <a class="site-footer__link meta" href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">LinkedIn<span class="visually-hidden"> (se abre en otra pestaña)</span></a>
                <?php endif; ?>
                <span class="meta"><?= $year ?></span>
            </nav>
        </div>
    </div>
</footer>

<?= partial('project-modal', ['project' => $openProject]) ?>

<?php // Resultado de la última acción del backend (envío del formulario de
      // contacto). Va al final: es una capa sobre la página, no parte de ella. ?>
<?php if (is_array($flash ?? null)): ?>
    <?= partial('alert', ['type' => $flash['type'], 'text' => $flash['text']]) ?>
<?php endif; ?>

<?php // Si el módulo no llega a cargar, se retira `js`: los estados ocultos
      // (apariciones, nombre del hero) se muestran como sin JavaScript. ?>
<script type="module" src="<?= asset('js/main.js') ?>" onerror="document.documentElement.classList.remove('js')"></script>
</body>
</html>
