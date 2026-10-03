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

/** Atributos de error de un campo: lo marcan inválido y lo atan a su mensaje. */
$invalid = static function (string $field) use ($errors): string {
    return isset($errors[$field])
        ? ' aria-invalid="true" aria-describedby="' . $field . '-error"'
        : '';
};
?>
<section class="section" id="contacto">
    <div class="container section__grid">
        <?= partial('section-header', [
            'title' => 'Contacto',
            'lead'  => '¿Tienes una vacante o un proyecto en el que podría aportar? Escríbeme y respondo en menos de 24 horas.',
        ]) ?>

        <div class="section__body contact">
            <?php if (! empty($profile['email']) || ! empty($profile['linkedin_url']) || ! empty($profile['github_url'])): ?>
                <ul class="contact__direct" aria-label="Vías directas" data-reveal>
                    <?php if (! empty($profile['email'])): ?>
                        <li><a href="mailto:<?= e($profile['email']) ?>" data-cursor="Escribir"><?= e($profile['email']) ?></a></li>
                    <?php endif; ?>
                    <?php if (! empty($profile['linkedin_url'])): ?>
                        <li><a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener" data-cursor="Abrir">LinkedIn<span class="visually-hidden"> (se abre en otra pestaña)</span></a></li>
                    <?php endif; ?>
                    <?php if (! empty($profile['github_url'])): ?>
                        <li><a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" data-cursor="Abrir">GitHub<span class="visually-hidden"> (se abre en otra pestaña)</span></a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>

            <?php // El resultado del envío no se pinta aquí: sale como alerta
                  // sobre la página, desde el layout. ?>
            <form class="form contact__form" method="post" action="<?= url('/contacto') ?>" novalidate data-reveal style="--i: 1">
                <div class="form__row">
                    <label class="form__label" for="name">Nombre</label>
                    <input class="form__input"<?= $invalid('name') ?>
                           type="text" id="name" name="name" required
                           autocomplete="name"
                           value="<?= e($old['name'] ?? '') ?>">
                    <?php if (isset($errors['name'])): ?>
                        <p class="form__error" id="name-error"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form__row">
                    <label class="form__label" for="email">Correo</label>
                    <input class="form__input"<?= $invalid('email') ?>
                           type="email" id="email" name="email" required
                           autocomplete="email"
                           value="<?= e($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?>
                        <p class="form__error" id="email-error"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form__row">
                    <label class="form__label" for="subject">Asunto <span class="form__optional">(opcional)</span></label>
                    <input class="form__input"<?= $invalid('subject') ?> type="text" id="subject" name="subject"
                           value="<?= e($old['subject'] ?? '') ?>">
                    <?php if (isset($errors['subject'])): ?>
                        <p class="form__error" id="subject-error"><?= e($errors['subject']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form__row">
                    <label class="form__label" for="message">Mensaje</label>
                    <textarea class="form__input form__input--area"<?= $invalid('message') ?>
                              id="message" name="message" rows="6" required><?= e($old['message'] ?? '') ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <p class="form__error" id="message-error"><?= e($errors['message']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Honeypot anti-bots: invisible para personas. -->
                <div class="form__honeypot" aria-hidden="true">
                    <label for="website">No llenar</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <button class="cta cta--primary form__submit" type="submit"><?= roll_text('Enviar mensaje') ?></button>
            </form>
        </div>
    </div>
</section>
