<?php
/**
 * Sección Contacto — cierre del scroll. El POST vuelve aquí con flash.
 *
 * @var array<string, mixed> $profile
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var array{type: string, text: string}|null $flash
 */

$errors = $errors ?? [];
$old    = $old ?? [];
$flash  = $flash ?? null;
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
                <?php if ($flash): ?>
                    <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></p>
                <?php endif; ?>

                <form class="form" method="post" action="<?= url('/contacto') ?>" novalidate>
                    <div class="form__row">
                        <label class="form__label meta" for="name">Nombre</label>
                        <input class="form__input<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                               type="text" id="name" name="name" required
                               autocomplete="name"
                               value="<?= e($old['name'] ?? '') ?>">
                        <?php if (isset($errors['name'])): ?>
                            <p class="form__error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="email">Correo</label>
                        <input class="form__input<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                               type="email" id="email" name="email" required
                               autocomplete="email"
                               value="<?= e($old['email'] ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <p class="form__error"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="subject">Asunto <span class="form__optional">opcional</span></label>
                        <input class="form__input" type="text" id="subject" name="subject"
                               value="<?= e($old['subject'] ?? '') ?>">
                    </div>

                    <div class="form__row">
                        <label class="form__label meta" for="message">Mensaje</label>
                        <textarea class="form__input form__input--area<?= isset($errors['message']) ? ' is-invalid' : '' ?>"
                                  id="message" name="message" rows="6" required
                                  placeholder="Cuéntame qué necesitas, con tus palabras. Con que me expliques el problema es suficiente."><?= e($old['message'] ?? '') ?></textarea>
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
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">Correo</dt>
                            <dd class="spec-list__val"><a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">Ubicación</dt>
                            <dd class="spec-list__val"><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['linkedin_url'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">LinkedIn</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">Ver perfil</a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['github_url'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key meta">GitHub</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">Ver repositorios</a></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <p class="contact__note">Tu mensaje me llega directo a mí. No lo comparto con nadie ni lo uso para mandarte publicidad.</p>
            </aside>

        </div>
    </div>
</section>
