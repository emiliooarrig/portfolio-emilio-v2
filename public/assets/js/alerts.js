/**
 * alerts.js — comportamiento de las alertas del sitio.
 *
 * La alerta la dibuja el backend (`partials/alert.php`) como un <dialog> ya
 * abierto, así que sin JavaScript se ve igual y se cierra igual: su botón
 * usa `method="dialog"`, que es nativo. Este módulo sólo añade lo que el
 * HTML por sí solo no da:
 *
 *   - la asciende a modal, que atrapa el foco, deja inerte lo de detrás y
 *     habilita Escape;
 *   - la cierra al hacer clic fuera del panel;
 *   - cierra sola las de éxito, que no piden respuesta — con una barra que
 *     enseña cuánto queda, y que se detiene si el puntero o el teclado
 *     entran en la alerta: si la estás leyendo, no se va;
 *   - le da salida animada en vez de un corte seco.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

/** Lo que dura visible una alerta que se cierra sola. */
const AUTO_CLOSE = 4200;

/** Tipos que se van solos: los demás esperan a que alguien los lea. */
const SELF_CLOSING = ['success'];

/** Respaldo por si `animationend` no llega (pestaña en segundo plano). */
const EXIT_TIMEOUT = 500;

export function initAlerts() {
    document.querySelectorAll('dialog[data-alert]').forEach(setUp);

    // Borrar no lleva a ninguna pantalla: pregunta aquí mismo. Va por
    // delegación porque las filas son muchas y todas preguntan igual.
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest?.('[data-confirm]');

        if (! trigger || event.defaultPrevented || event.metaKey || event.ctrlKey) {
            return;
        }

        // Sin plantilla no hay pregunta que hacer: que el enlace siga su
        // camino a la pantalla de confirmación, que es el respaldo.
        if (! document.querySelector('template[data-alert-template="confirm"]')) {
            return;
        }

        event.preventDefault();
        ask(trigger);
    });
}

/**
 * Clona la plantilla de la pregunta, la llena con lo que trae el disparador
 * y la abre. Al aceptar, envía el borrado por POST; al cancelar, se retira
 * sin dejar rastro en el DOM.
 */
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

    // Es un clon: cuando se cierra desaparece, y la siguiente pregunta parte
    // otra vez de la plantilla. Se engancha después de `setUp()`, que abre el
    // diálogo pasando por `close()`: antes, este mismo listener lo sacaría del
    // DOM en el camino.
    dialog.addEventListener('close', () => dialog.remove());
}

/**
 * El borrado es escritura: va por POST y con token, igual que desde la
 * pantalla de confirmación. La misma URL sirve para las dos cosas — por GET
 * pregunta, por POST borra —, así que basta con la del enlace.
 */
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

function setUp(dialog) {
    toModal(dialog);

    // Los botones cierran por su cuenta; aquí sólo se interceptan para que la
    // salida se vea, y por eso el respaldo nativo sigue intacto si algo falla.
    dialog.querySelector('[data-alert-dismiss]')?.addEventListener('click', (event) => {
        event.preventDefault();
        dismiss(dialog);
    });

    // Clic en el velo: el panel es un hijo, así que sólo cuenta cuando el
    // clic cae en el diálogo mismo.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dismiss(dialog);
        }
    });

    // Escape lo cierra el navegador de golpe: se le quita para animar la salida.
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        dismiss(dialog);
    });

    if (SELF_CLOSING.includes(dialog.dataset.alertType)) {
        countDown(dialog);
    }
}

/**
 * Un <dialog open> escrito en el HTML no es modal: no atrapa el foco ni deja
 * inerte la página. Se cierra y se vuelve a abrir como modal, que sí.
 */
function toModal(dialog) {
    if (typeof dialog.showModal === 'function') {
        try {
            // `close()` sólo hace algo si venía abierto del HTML; un clon de la
            // plantilla llega cerrado y `showModal()` es quien lo abre.
            dialog.close();
            dialog.showModal();
        } catch {
            dialog.open = true;
        }
    } else {
        dialog.open = true;
    }

    focusConfirm(dialog);
}

/**
 * El foco entra en el botón, que es la única salida de la alerta.
 *
 * Se repite al terminar de cargar porque una URL con ancla (`/#contacto`,
 * que es a donde vuelve el formulario de contacto) mueve el foco a la
 * sección después de que el diálogo se abra, y la alerta se quedaría sin él.
 */
function focusConfirm(dialog) {
    const confirm = () => dialog.open && dialog.querySelector('[data-alert-dismiss]')?.focus();

    confirm();

    if (document.readyState !== 'complete') {
        window.addEventListener('load', confirm, { once: true });
    }
}

function countDown(dialog) {
    const bar = document.createElement('span');

    bar.className = 'alert__timer';
    bar.setAttribute('aria-hidden', 'true');
    dialog.style.setProperty('--alert-timer', `${AUTO_CLOSE}ms`);
    dialog.querySelector('.alert__panel')?.appendChild(bar);

    let timer = window.setTimeout(() => dismiss(dialog), AUTO_CLOSE);

    // Quien se acerca a leerla se queda con ella: la cuenta atrás se cancela
    // y ya sólo se cierra a mano.
    const hold = () => {
        window.clearTimeout(timer);
        timer = 0;
        bar.remove();
    };

    // Se mira el puntero y el teclado, no el foco: el foco lo pone este mismo
    // módulo al abrir, y cancelaría la cuenta atrás antes de empezar.
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
