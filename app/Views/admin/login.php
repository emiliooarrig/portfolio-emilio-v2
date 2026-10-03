<?php
/**
 * Acceso al panel. Usuario (no correo) y contraseña, nada más: las cuentas
 * se crean desde dentro, así que no hay enlace de registro.
 *
 * @var string|null $error     motivo del intento anterior
 * @var string|null $notice    aviso neutro (sesión cerrada, sesión caducada)
 * @var string      $oldUser   usuario tecleado, para no volver a escribirlo
 * @var string      $token     token anti-CSRF de esta sesión
 * @var array<string, mixed> $profile
 */
?>
<?php // Lo que traiga el intento anterior sale como alerta sobre la tarjeta. ?>
<?php if ($error): ?>
    <?= partial('alert', ['type' => 'error', 'text' => $error]) ?>
<?php elseif ($notice): ?>
    <?= partial('alert', ['type' => 'success', 'text' => $notice]) ?>
<?php endif; ?>

<div class="auth__card">

    <div class="auth__brand">
        <span class="auth__brand-name meta"><?= e($profile['full_name']) ?></span>
    </div>

    <h1 class="auth__title">Panel de administración</h1>
    <p class="auth__lead">Entra con tu usuario para editar el contenido del sitio.</p>


    <form class="form" method="post" action="<?= url('/admin/login') ?>" novalidate>
        <input type="hidden" name="_token" value="<?= e($token) ?>">

        <div class="form__row">
            <label class="form__label meta" for="username">Usuario</label>
            <input class="form__input"
                   type="text" id="username" name="username" required
                   autocomplete="username"
                   autocapitalize="none"
                   spellcheck="false"
                   <?= $oldUser === '' ? 'autofocus' : '' ?>
                   value="<?= e($oldUser) ?>">
        </div>

        <div class="form__row">
            <label class="form__label meta" for="password">Contraseña</label>
            <input class="form__input"
                   type="password" id="password" name="password" required
                   autocomplete="current-password"
                   <?= $oldUser === '' ? '' : 'autofocus' ?>>
        </div>

        <?php // El bloqueo por intentos no deshabilita el botón: lo aplica el
              // servidor y avisa cuántos segundos faltan, para no dejar la
              // pantalla en un callejón sin salida si no hay JS. ?>
        <button class="cta cta--primary auth__submit" type="submit">
            Entrar
        </button>
    </form>

    <p class="auth__note">
        No hay registro público: las cuentas se dan de alta desde el propio panel.
    </p>

    <a class="auth__back" href="<?= url('/') ?>">Volver al sitio</a>

</div>
