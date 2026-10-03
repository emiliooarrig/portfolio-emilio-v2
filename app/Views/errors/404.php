<?php
/**
 * 404 — la landing es una sola página, así que todo lleva de vuelta a ella.
 *
 * @var string $message
 */
?>
<section class="section">
    <div class="container error-page">
        <p class="meta">404</p>
        <h1 class="error-page__title">Esta página no existe.</h1>
        <p class="error-page__text"><?= e($message ?? 'Puede que el enlace esté mal escrito o que ya no esté disponible.') ?></p>
        <a class="cta cta--primary" href="<?= url('/') ?>">Volver al inicio</a>
    </div>
</section>
