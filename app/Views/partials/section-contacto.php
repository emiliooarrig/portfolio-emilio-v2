<?php
/**
 * Sección Contacto — cierre del scroll. El POST vuelve aquí con los errores
 * de cada campo; el resultado del envío lo anuncia la alerta del layout.
 *
 * @var array<string, mixed> $profile
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */

$errors = $errors ?? [];
$old    = $old ?? [];

/**
 * Íconos inline, en el mismo trazo que el resto del sitio.
 *
 * Los de marca (LinkedIn, GitHub) son la excepción: su logo es una silueta
 * rellena, y redibujarlos a línea los volvería irreconocibles. Por eso cada
 * ícono dice si es de marca y el helper elige relleno o trazo.
 */
$icons = [
    'user'    => '<circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c0-3.6 3.1-5.6 7-5.6s7 2 7 5.6"></path>',
    'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3.6 7.2 8.4 5.6 8.4-5.6"></path>',
    'tag'     => '<path d="M11.6 3H4.5A1.5 1.5 0 0 0 3 4.5v7.1a2 2 0 0 0 .6 1.4l7.4 7.4a2 2 0 0 0 2.8 0l6.5-6.5a2 2 0 0 0 0-2.8L13 3.6a2 2 0 0 0-1.4-.6Z"></path><path d="M7.5 7.5h.01"></path>',
    'message' => '<path d="M20 4H4a1.5 1.5 0 0 0-1.5 1.5v9A1.5 1.5 0 0 0 4 16h2.5v4L11 16h9a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 20 4Z"></path><path d="M7.5 8.5h9M7.5 12h6"></path>',
    'pin'     => '<path d="M12 21s7-5.4 7-10.5A7 7 0 0 0 5 10.5C5 15.6 12 21 12 21Z"></path><circle cx="12" cy="10.3" r="2.6"></circle>',
    'linkedin' => ['brand' => true, 'path' => '<path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.13 1.44-2.13 2.94v5.66H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13Zm1.78 13.02H3.55V9h3.57v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"></path>'],
    'github'   => ['brand' => true, 'path' => '<path d="M12 .3a12 12 0 0 0-3.79 23.4c.6.1.82-.26.82-.58l-.01-2.04c-3.34.73-4.05-1.6-4.05-1.6-.54-1.39-1.33-1.76-1.33-1.76-1.09-.75.08-.73.08-.73 1.21.08 1.84 1.24 1.84 1.24 1.07 1.83 2.81 1.3 3.5 1 .1-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.11-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.29-1.55 3.29-1.23 3.29-1.23.65 1.66.24 2.88.12 3.18.77.84 1.23 1.91 1.23 3.22 0 4.61-2.8 5.63-5.48 5.92.43.37.81 1.1.81 2.22l-.01 3.29c0 .32.21.69.82.57A12 12 0 0 0 12 .3Z"></path>'],
];

/** Devuelve el <svg> de un ícono; cadena vacía si el nombre no existe. */
$icon = static function (string $name, int $size = 18) use ($icons): string {
    $glyph = $icons[$name] ?? null;

    if ($glyph === null) {
        return '';
    }

    $brand = is_array($glyph);
    $paths = $brand ? $glyph['path'] : $glyph;

    return sprintf(
        '<svg viewBox="0 0 24 24" width="%1$d" height="%1$d" %2$s aria-hidden="true">%3$s</svg>',
        $size,
        $brand
            ? 'fill="currentColor"'
            : 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"',
        $paths
    );
};
?>
<section class="section" id="contacto">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Contacto',
            'title'   => 'Cuéntame qué necesitas',
            'lead'    => 'No hace falta que sepas de tecnología ni que traigas todo claro: dime qué te está costando trabajo y yo te digo si puedo ayudarte. Contesto en menos de 24 horas.',
        ]) ?>

        <div class="bento bento--contact reveal--stagger">

            <div class="bento-cell bento-cell--form bento-card--hoverable">
                <?php // El resultado del envío no se pinta aquí: sale como alerta
                      // sobre la página, desde el layout. ?>
                <form class="form" method="post" action="<?= url('/contacto') ?>" novalidate>
                    <div class="form__row">
                        <label class="form__label meta" for="name">Nombre</label>
                        <div class="form__field<?= isset($errors['name']) ? ' is-invalid' : '' ?>">
                            <span class="form__icon"><?= $icon('user') ?></span>
                            <input class="form__input form__input--iconed<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                                   type="text" id="name" name="name" required
                                   autocomplete="name"
                                   value="<?= e($old['name'] ?? '') ?>">
                        </div>
                        <?php if (isset($errors['name'])): ?>
                            <p class="form__error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="email">Correo</label>
                        <div class="form__field<?= isset($errors['email']) ? ' is-invalid' : '' ?>">
                            <span class="form__icon"><?= $icon('mail') ?></span>
                            <input class="form__input form__input--iconed<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                                   type="email" id="email" name="email" required
                                   autocomplete="email"
                                   value="<?= e($old['email'] ?? '') ?>">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <p class="form__error"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="subject">Asunto <span class="form__optional">opcional</span></label>
                        <div class="form__field">
                            <span class="form__icon"><?= $icon('tag') ?></span>
                            <input class="form__input form__input--iconed" type="text" id="subject" name="subject"
                                   value="<?= e($old['subject'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="message">Mensaje</label>
                        <div class="form__field<?= isset($errors['message']) ? ' is-invalid' : '' ?>">
                            <span class="form__icon form__icon--area"><?= $icon('message') ?></span>
                            <textarea class="form__input form__input--area form__input--iconed<?= isset($errors['message']) ? ' is-invalid' : '' ?>"
                                      id="message" name="message" rows="6" required
                                      placeholder="Cuéntame qué necesitas, con tus palabras. Con que me expliques el problema es suficiente."><?= e($old['message'] ?? '') ?></textarea>
                        </div>
                        <?php if (isset($errors['message'])): ?>
                            <p class="form__error"><?= e($errors['message']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Honeypot anti-bots: invisible para personas. -->
                    <div class="form__honeypot" aria-hidden="true">
                        <label for="website">No llenar</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button class="cta cta--primary form__submit" type="submit">
                        Enviar mensaje
                        <span class="cta__icon" aria-hidden="true">→</span>
                    </button>
                </form>
            </div>

            <aside class="bento-cell bento-cell--direct bento-card--hoverable">
                <p class="eyebrow meta">Directo</p>

                <dl class="spec-list">
                    <?php if (! empty($profile['email'])): ?>
                        <div class="spec-list__row spec-list__row--link">
                            <dt class="spec-list__key meta"><span class="spec-list__icon"><?= $icon('mail', 15) ?></span>Correo</dt>
                            <dd class="spec-list__val"><a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta"><span class="spec-list__icon"><?= $icon('pin', 15) ?></span>Ubicación</dt>
                            <dd class="spec-list__val"><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['linkedin_url'])): ?>
                        <div class="spec-list__row spec-list__row--link">
                            <dt class="spec-list__key meta"><span class="spec-list__icon"><?= $icon('linkedin', 14) ?></span>LinkedIn</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">Ver perfil</a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['github_url'])): ?>
                        <div class="spec-list__row spec-list__row--link">
                            <dt class="spec-list__key meta"><span class="spec-list__icon"><?= $icon('github', 14) ?></span>GitHub</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">Ver repositorios</a></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <p class="contact__note">Tu mensaje me llega directo a mí. No lo comparto con nadie ni lo uso para mandarte publicidad.</p>
            </aside>

        </div>
    </div>
</section>
