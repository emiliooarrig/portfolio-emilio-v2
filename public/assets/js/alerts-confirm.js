/**
 * alerts-confirm.js — sólo el panel: borrar pregunta en una alerta.
 *
 * Cada [data-confirm] (las papeleras de las tablas) clona el molde que deja
 * `layouts/admin.php`, lo rellena con `data-confirm-title` / `-text` y, al
 * aceptar, envía el borrado por POST con el token del panel. Sin JS, el
 * enlace lleva a la pantalla de confirmación, que es el respaldo.
 */

import { setUp } from './alerts.js';

export { initAlerts } from './alerts.js';

export function initConfirm() {
    // Por delegación: las filas son muchas y todas preguntan igual.
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest?.('[data-confirm]');

        if (! trigger || event.defaultPrevented || event.metaKey || event.ctrlKey) {
            return;
        }

        if (! document.querySelector('template[data-alert-template="confirm"]')) {
            return;
        }

        event.preventDefault();
        ask(trigger);
    });
}

function ask(trigger) {
    const template = document.querySelector('template[data-alert-template="confirm"]');
    const dialog = template.content.firstElementChild.cloneNode(true);

    dialog.querySelector('.alert__title').textContent = trigger.dataset.confirmTitle || '¿Borrar?';
    dialog.querySelector('.alert__text').textContent = trigger.dataset.confirmText || 'No hay deshacer.';

    dialog.querySelector('[data-alert-accept]')?.addEventListener('click', (event) => {
        event.preventDefault();
        send(trigger.getAttribute('href'));
    });

    document.body.appendChild(dialog);
    setUp(dialog);

    // Es un clon: al cerrarse desaparece. Se engancha después de setUp(),
    // que abre el diálogo pasando por close().
    dialog.addEventListener('close', () => dialog.remove());
}

/** Por GET la URL pregunta; por POST, con token, borra. */
function send(action) {
    const form = document.createElement('form');
    const token = document.createElement('input');

    form.method = 'post';
    form.action = action;
    form.hidden = true;

    token.type = 'hidden';
    token.name = '_token';
    token.value = document.querySelector('[data-token]')?.dataset.token || '';

    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
