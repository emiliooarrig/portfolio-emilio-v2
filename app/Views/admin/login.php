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
<div class="auth__card">

    <div class="auth__brand">
        <span class="auth__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                <path d="M14 17.5h7M17.5 14v7"></path>
            </svg>
        </span>
        <span class="auth__brand-name meta"><?= e($profile['full_name']) ?></span>
    </div>

    <h1 class="auth__title">Panel de administración</h1>
    <p class="auth__lead">Entra con tu usuario para editar el contenido del sitio.</p>

    <?php if ($error): ?>
        <p class="flash flash--error" role="alert"><?= e($error) ?></p>
    <?php elseif ($notice): ?>
        <p class="flash flash--success" role="status"><?= e($notice) ?></p>
    <?php endif; ?>

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
            <span class="cta__icon" aria-hidden="true">→</span>
        </button>
    </form>

    <p class="auth__note">
        No hay registro público: las cuentas se dan de alta desde el propio panel.
    </p>

    <a class="auth__back" href="<?= url('/') ?>">← Volver al sitio</a>

</div>
