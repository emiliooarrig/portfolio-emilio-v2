<?php
/**
 * 404 — la landing es una sola página, así que todo lleva de vuelta a ella.
 *
 * @var string $message
 */
?>
<section class="section section--error">
    <div class="container">
        <p class="error-code meta">404</p>
        <h1 class="error-title">Aquí no hay nada</h1>
        <p class="error-text"><?= e($message ?? 'Esta página no existe. Puede que el enlace esté mal escrito o que ya no esté disponible.') ?></p>

        <div class="error-actions">
            <a class="cta cta--primary" href="<?= url('/') ?>">Volver al inicio</a>
            <a class="cta cta--secondary" href="<?= url('/') ?>#servicios">Ver en qué te ayudo</a>
        </div>
    </div>
</section>
