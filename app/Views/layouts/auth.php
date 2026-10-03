<?php
/**
 * Layout del panel: sin nav de la landing, sin footer y sin JS.
 *
 * La landing y el panel no comparten cabecera a propósito — quien entra
 * aquí ya no está navegando el sitio, está trabajando sobre él.
 *
 * @var array<string, mixed> $profile
 * @var string $content
 * @var string $pageTitle
 * @var int    $year
 */

$title = $pageTitle !== '' ? $pageTitle . ' — ' . $profile['full_name'] : (string) $profile['full_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <?php // El panel no se indexa ni se comparte: no es parte del sitio público. ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#F7F8F9" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#111315" media="(prefers-color-scheme: dark)">

    <link rel="preload" href="<?= url('/assets/fonts/mona-sans.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="page page--auth">

<main class="auth">
    <?= $content ?>
</main>

<?php // Igual que en el panel: la alerta ya funciona sin JavaScript y esto
      // sólo la asciende a modal. ?>
<script type="module">
    import { initAlerts } from '<?= asset('js/alerts.js') ?>';

    initAlerts();
</script>

</body>
</html>
