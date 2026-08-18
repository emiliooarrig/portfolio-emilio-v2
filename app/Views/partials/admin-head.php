<?php
/**
 * Cabecera de una pantalla del panel: qué es esta tabla, de dónde sale y la
 * única acción que la abre — dar de alta.
 *
 * @var string $title
 * @var string $lead    una línea: qué sección de la landing alimenta
 * @var string $meta    opcional, el conteo de filas
 * @var array{label: string, href: string} $action  opcional, el botón de alta
 */
$action = $action ?? [];
?>
<header class="admin-head">
    <div class="admin-head__text">
        <h1 class="admin-head__title"><?= e($title) ?></h1>
        <p class="admin-head__lead"><?= e($lead) ?></p>
    </div>

    <div class="admin-head__aside">
        <?php if (! empty($meta)): ?>
            <p class="admin-head__meta meta"><?= e($meta) ?></p>
        <?php endif; ?>

        <?php if ($action !== []): ?>
            <a class="cta cta--primary cta--sm" href="<?= url($action['href']) ?>"><?= e($action['label']) ?></a>
        <?php endif; ?>
    </div>
</header>
