/**
 * alerts.js — comportamiento de las alertas (`partials/alert.php`).
 *
 * La alerta llega como <dialog open> y sin JS se ve y se cierra igual
 * (`method="dialog"`). Aquí sólo se asciende a modal (foco atrapado,
 * Escape), se cierra al hacer clic fuera, se anima la salida y las de
 * éxito se cierran solas salvo que el puntero o el teclado entren en ellas.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');
const AUTO_CLOSE = 4200;        // lo que dura visible una alerta de éxito
const SELF_CLOSING = ['success'];
const EXIT_TIMEOUT = 500;       // por si `animationend` no llega

export function initAlerts() {
    document.querySelectorAll('dialog[data-alert]').forEach(setUp);
}

export function setUp(dialog) {
    toModal(dialog);

    // Los botones cierran solos (nativo); se interceptan sólo para animar.
    dialog.querySelector('[data-alert-dismiss]')?.addEventListener('click', (event) => {
        event.preventDefault();
        dismiss(dialog);
    });

    // Clic en el velo: sólo cuenta si cae en el diálogo, no en el panel.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dismiss(dialog);
        }
    });

    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        dismiss(dialog);
    });

    if (SELF_CLOSING.includes(dialog.dataset.alertType)) {
        countDown(dialog);
    }
}

/** Un <dialog open> del HTML no es modal: se cierra y se reabre como tal. */
function toModal(dialog) {
    try {
        dialog.close();
        dialog.showModal();
    } catch {
        dialog.open = true;
    }

    // Se repite al cargar: un ancla (/#contacto) mueve el foco después.
    const focus = () => dialog.open && dialog.querySelector('[data-alert-dismiss]')?.focus();

    focus();

    if (document.readyState !== 'complete') {
        window.addEventListener('load', focus, { once: true });
    }
}

function countDown(dialog) {
    const bar = document.createElement('span');

    bar.className = 'alert__timer';
    bar.setAttribute('aria-hidden', 'true');
    dialog.style.setProperty('--alert-timer', `${AUTO_CLOSE}ms`);
    dialog.querySelector('.alert__panel')?.appendChild(bar);

    const timer = window.setTimeout(() => dismiss(dialog), AUTO_CLOSE);

    // Quien se acerca a leerla se queda con ella. Puntero y teclado, no el
    // foco: el foco lo pone este módulo al abrir.
    const hold = () => {
        window.clearTimeout(timer);
        bar.remove();
    };

    dialog.addEventListener('pointerenter', hold, { once: true });
    dialog.addEventListener('keydown', hold, { once: true });
}

function dismiss(dialog) {
    if (dialog.classList.contains('is-closing')) {
        return;
    }

    dialog.classList.add('is-closing');

    const close = () => dialog.close();

    if (REDUCED_MOTION.matches) {
        close();

        return;
    }

    dialog.addEventListener('animationend', close, { once: true });
    window.setTimeout(close, EXIT_TIMEOUT);
}
