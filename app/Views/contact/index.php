<?php
/**
 * Contacto.
 *
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var array{type: string, text: string}|null $flash
 * @var array<string, mixed> $profile
 */

$old = $old ?? [];
?>
<section class="section">
    <div class="container">
        <?= partial('section-header', [
            'eyebrow' => 'Contacto',
            'title'   => 'Hablemos de tus datos',
            'lead'    => 'Consultoría, proyecto puntual o posición de tiempo completo: escribe qué necesitas y te respondo en menos de 24 horas.',
        ]) ?>

        <div class="grid-split grid-split--form">
            <div class="panel panel--form">
                <?php if (! empty($flash)): ?>
                    <p class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></p>
                <?php endif; ?>

                <form class="form" method="post" action="<?= url('/contacto') ?>" novalidate>
                    <div class="form__row">
                        <label class="form__label mono" for="name">Nombre</label>
                        <input class="form__input<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                               type="text" id="name" name="name" required
                               autocomplete="name"
                               value="<?= e($old['name'] ?? '') ?>">
                        <?php if (isset($errors['name'])): ?>
                            <p class="form__error"><?= e($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label mono" for="email">Correo</label>
                        <input class="form__input<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                               type="email" id="email" name="email" required
                               autocomplete="email"
                               value="<?= e($old['email'] ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <p class="form__error"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form__row">
                        <label class="form__label mono" for="subject">Asunto <span class="form__optional">opcional</span></label>
                        <input class="form__input" type="text" id="subject" name="subject"
                               value="<?= e($old['subject'] ?? '') ?>">
                    </div>

                    <div class="form__row">
                        <label class="form__label mono" for="message">Mensaje</label>
                        <textarea class="form__input form__input--area<?= isset($errors['message']) ? ' is-invalid' : '' ?>"
                                  id="message" name="message" rows="6" required
                                  placeholder="¿Qué estás midiendo hoy y en qué se atora?"><?= e($old['message'] ?? '') ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <p class="form__error"><?= e($errors['message']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Honeypot anti-bots: invisible para personas. -->
                    <div class="form__honeypot" aria-hidden="true">
                        <label for="website">No llenar</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button class="cta cta--primary form__submit" type="submit">Enviar mensaje</button>
                </form>
            </div>

            <aside class="panel panel--sticky">
                <p class="eyebrow mono">Directo</p>

                <dl class="spec-list">
                    <?php if (! empty($profile['email'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">Correo</dt>
                            <dd class="spec-list__val"><a href="mailto:<?= e($profile['email']) ?>"><?= e($profile['email']) ?></a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['location'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">Ubicación</dt>
                            <dd class="spec-list__val"><?= e($profile['location']) ?></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['linkedin_url'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">LinkedIn</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener">Ver perfil</a></dd>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($profile['github_url'])): ?>
                        <div class="spec-list__row">
                            <dt class="spec-list__key mono">GitHub</dt>
                            <dd class="spec-list__val"><a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener">Ver repositorios</a></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <p class="panel__note">Los mensajes llegan a la bandeja del panel de administración; no se comparten con terceros.</p>
            </aside>
        </div>
    </div>
</section>
