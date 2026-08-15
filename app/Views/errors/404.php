<?php
/**
 * 404.
 *
 * @var string $message
 */
?>
<section class="section section--error">
    <div class="container">
        <p class="error-code mono">404</p>
        <h1 class="error-title">Ruta sin destino</h1>
        <p class="error-text"><?= e($message ?? 'La página que buscas no existe.') ?></p>

        <div class="hero__actions">
            <a class="cta cta--primary" href="<?= url('/') ?>">Volver al inicio</a>
            <a class="cta cta--secondary" href="<?= url('/proyectos') ?>">Ver proyectos</a>
        </div>
    </div>
</section>
