<?php
/**
 * Layout del panel: barra lateral fija + una pantalla por sección.
 *
 * No comparte nada con `main.php` a propósito — quien entra aquí ya no está
 * navegando el sitio, está trabajando sobre él. Sin nav público, sin footer,
 * sin JS y sin indexar. `auth.php` sigue siendo el del login: ahí todavía no
 * hay sesión, así que no puede haber barra lateral.
 *
 * @var array<string, mixed>      $profile
 * @var array<string, mixed>|null $admin
 * @var array<string, string>|null $flash  resultado de la acción anterior
 * @var string $token
 * @var string $activePath
 * @var string $content
 * @var string $pageTitle
 */

$title = $pageTitle !== '' ? $pageTitle . ' · Panel' : 'Panel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <?php // El panel no se indexa ni se comparte: no es parte del sitio público. ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0A0E15">

    <link rel="preload" href="<?= url('/assets/fonts/stack-sans-headline.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= url('/assets/fonts/sekuya.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="page page--admin">

<a class="skip-link" href="#panel">Ir al contenido</a>

<div class="admin" data-token="<?= e($token) ?>">

    <?= partial('admin-sidebar', [
        'profile'    => $profile,
        'admin'      => $admin,
        'token'      => $token,
        'activePath' => $activePath,
    ]) ?>

    <main class="admin__main" id="panel">

        <?= $content ?>
    </main>

</div>

<?php // Lo que pasó con la acción anterior. Vive en la sesión y se borra al
      // leerse, así que recargar no la repite. ?>
<?php if (is_array($flash)): ?>
    <?= partial('alert', ['type' => $flash['type'], 'text' => $flash['text']]) ?>
<?php endif; ?>

<?php // Molde de la pregunta de borrado: `alerts.js` lo clona cada vez que hay
      // que confirmar algo, así el diálogo se dibuja una sola vez y en PHP. ?>
<?= partial('alert', [
    'type'     => 'warning',
    'title'    => '¿Borrar?',
    'text'     => '',
    'confirm'  => 'Sí, borrar',
    'cancel'   => 'Cancelar',
    'template' => 'confirm',
]) ?>

<?php // El panel funciona sin JavaScript: la alerta ya se ve y se cierra sola
      // con su botón, y borrar sigue teniendo su pantalla de confirmación.
      // Esto sólo asciende la alerta a modal y convierte el borrado en pregunta. ?>
<script type="module">
    import { initAlerts } from '<?= asset('js/alerts.js') ?>';

    initAlerts();
</script>

</body>
</html>
