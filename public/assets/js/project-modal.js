/**
 * project-modal.js — detalle de proyecto como capa sobre la landing.
 *
 * La página nunca se recarga: el fragmento llega por fetch y la URL se
 * actualiza con history.pushState sólo para que el enlace sea compartible.
 * Sin JS, la fila sigue siendo un enlace normal a /proyectos/{slug}, que el
 * servidor responde con la landing completa y el modal ya abierto.
 *
 * Con View Transitions (y sin movimiento reducido) el título de la fila
 * "viaja" al encabezado del detalle al abrir, y de vuelta al cerrar. El
 * nombre `project-title` debe ser único en cada momento: lo lleva la fila
 * o el detalle, nunca los dos. Sin la API: fundido de opacidad.
 */

const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'textarea',
    'input:not([type="hidden"])',
    'select',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

const TRANSITION_NAME = 'project-title';
const CLOSE_MS = 200;

const LOADING_HTML = '<p class="project-modal__loading meta">Cargando proyecto…</p>';

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

const canMorph = () => typeof document.startViewTransition === 'function' && !REDUCED_MOTION.matches;

/** Título de la fila que enlaza a esa URL, si está a la vista. */
function rowTitleFor(url) {
    const path = new URL(url, window.location.href).pathname;

    const link = Array.from(document.querySelectorAll('[data-project-link]'))
        .find((el) => new URL(el.href, window.location.href).pathname === path);

    const title = link?.closest('[data-project-card]')?.querySelector('.project-row__title');

    if (!title) {
        return null;
    }

    const rect = title.getBoundingClientRect();

    return rect.bottom > 0 && rect.top < window.innerHeight ? title : null;
}

export function initProjectModal() {
    const modal = document.querySelector('[data-project-modal]');

    if (!modal) {
        return;
    }

    const dialog = modal.querySelector('[data-modal-dialog]');
    const content = modal.querySelector('[data-modal-body]');
    const base = document.body.dataset.base || '';
    const projectsUrl = `${base}/#proyectos`;
    const siteTitle = document.body.dataset.siteTitle || '';

    let isOpen = modal.dataset.modalInitial === 'open';
    let pushedEntry = false;   // ¿esta apertura añadió una entrada al historial?
    let ignoreNextPop = false; // el propio cierre disparó el popstate
    let pendingScroll = null;  // ancla a la que ir una vez cerrado el modal
    let lastFocus = null;
    let closeTimer = 0;
    let loadSeq = 0;           // descarta respuestas que llegan tarde
    let currentUrl = isOpen ? window.location.href : null; // proyecto abierto

    // ----------------------------------------------------------
    //  Piezas
    // ----------------------------------------------------------
    const lockScroll = (locked) => document.body.classList.toggle('is-locked', locked);

    const focusDialog = () => dialog?.focus({ preventScroll: true });

    const detailTitle = () => content.querySelector('[data-detail-title]');

    const showShell = ({ fade = false } = {}) => {
        window.clearTimeout(closeTimer);
        modal.classList.remove('is-closing');
        modal.classList.toggle('is-fading-in', fade);
        modal.hidden = false;
        lockScroll(true);
        isOpen = true;
    };

    const render = (html) => {
        content.innerHTML = html;
        content.scrollTop = 0;
        focusDialog();
    };

    /** Deja la página como estaba antes de abrir. */
    const finishClose = () => {
        modal.hidden = true;
        modal.classList.remove('is-closing', 'is-fading-in');
        content.innerHTML = '';
        lockScroll(false);

        // Al entrar directo, el <title> hablaba del proyecto: vuelve al del sitio.
        if (siteTitle && document.title !== siteTitle) {
            document.title = siteTitle;
        }

        if (lastFocus && document.contains(lastFocus)) {
            lastFocus.focus({ preventScroll: true });
        }

        lastFocus = null;
    };

    async function fetchDetail(url) {
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return await response.text();
        } catch (error) {
            // Si el fragmento no llega, el enlace directo sigue siendo válido.
            return `
                <p class="empty-state">
                    No se pudo cargar el proyecto.
                    <a href="${url}">Abrirlo en su propia página</a>.
                </p>`;
        }
    }

    // ----------------------------------------------------------
    //  Apertura / cierre
    // ----------------------------------------------------------
    async function open(url, { push = true } = {}) {
        const seq = ++loadSeq;

        currentUrl = url;

        if (!isOpen) {
            lastFocus = document.activeElement;
        }

        if (push) {
            history.pushState({ projectModal: url }, '', url);
            pushedEntry = true;
        }

        // Ya abierto (atrás/adelante entre proyectos) o sin la API: la capa
        // aparece de inmediato con su aviso de carga y el contenido llega después.
        if (isOpen || !canMorph()) {
            showShell({ fade: !isOpen && !REDUCED_MOTION.matches });
            content.innerHTML = LOADING_HTML;

            const html = await fetchDetail(url);

            if (seq === loadSeq && isOpen) {
                render(html);
            }

            return;
        }

        // Con View Transitions se espera al contenido: el título necesita
        // existir en los dos estados para poder viajar de uno a otro.
        document.documentElement.setAttribute('aria-busy', 'true');

        const html = await fetchDetail(url);

        document.documentElement.removeAttribute('aria-busy');

        if (seq !== loadSeq) {
            return;
        }

        const origin = rowTitleFor(url);

        if (origin) {
            origin.style.viewTransitionName = TRANSITION_NAME;
        }

        document.startViewTransition(() => {
            if (origin) {
                origin.style.viewTransitionName = '';
            }

            showShell();
            render(html);

            const title = detailTitle();

            if (title && origin) {
                title.style.viewTransitionName = TRANSITION_NAME;
            }
        });
    }

    /** Cierre visual puro: no toca el historial. */
    function hideShell() {
        if (!isOpen) {
            return;
        }

        isOpen = false;
        loadSeq++;

        if (canMorph()) {
            const title = detailTitle();
            // Con la URL del proyecto, no la actual: en un "atrás" del
            // navegador la barra ya muestra la de la landing.
            const origin = currentUrl ? rowTitleFor(currentUrl) : null;

            if (title) {
                title.style.viewTransitionName = origin ? TRANSITION_NAME : '';
            }

            const transition = document.startViewTransition(() => {
                finishClose();

                if (origin) {
                    origin.style.viewTransitionName = TRANSITION_NAME;
                }
            });

            transition.finished.finally(() => {
                if (origin) {
                    origin.style.viewTransitionName = '';
                }
            });

            return;
        }

        if (REDUCED_MOTION.matches) {
            finishClose();

            return;
        }

        modal.classList.add('is-closing');
        closeTimer = window.setTimeout(finishClose, CLOSE_MS);
    }

    /**
     * Salta al ancla pedida, pero sólo cuando el modal ya soltó el scroll del
     * body y el navegador terminó de restaurar la posición del historial.
     */
    function flushPendingScroll() {
        if (!pendingScroll) {
            return;
        }

        const target = document.querySelector(pendingScroll);

        pendingScroll = null;

        if (!target) {
            return;
        }

        requestAnimationFrame(() => requestAnimationFrame(() => {
            target.scrollIntoView({
                behavior: REDUCED_MOTION.matches ? 'auto' : 'smooth',
                block: 'start',
            });
        }));
    }

    /** Cierre pedido por el usuario: además de lo visual, ordena la URL. */
    function requestClose() {
        if (!isOpen) {
            return;
        }

        hideShell();

        if (pushedEntry) {
            // history.back() restaura la posición guardada: hay que esperarla.
            pushedEntry = false;
            ignoreNextPop = true;
            history.back();
        } else {
            history.replaceState({}, '', projectsUrl);
            window.setTimeout(flushPendingScroll, CLOSE_MS);
        }
    }

    /** Cierra y lleva el scroll a una sección de la landing. */
    function closeAndScroll(hash) {
        pendingScroll = hash;
        requestClose();
    }

    // ----------------------------------------------------------
    //  Disparadores
    // ----------------------------------------------------------
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-project-link]');

        if (trigger && !event.metaKey && !event.ctrlKey && !event.shiftKey && event.button === 0) {
            event.preventDefault();
            open(trigger.getAttribute('href'));
        }
    });

    modal.addEventListener('click', (event) => {
        const jump = event.target.closest('[data-modal-scroll]');

        if (jump) {
            event.preventDefault();
            closeAndScroll(jump.dataset.modalScroll);

            return;
        }

        if (event.target.closest('[data-modal-dismiss]')) {
            event.preventDefault();
            requestClose();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!isOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            requestClose();

            return;
        }

        // Trampa de foco: el tabulador no debe salir del diálogo.
        if (event.key === 'Tab' && dialog) {
            const items = Array.from(dialog.querySelectorAll(FOCUSABLE))
                .filter((el) => el.offsetParent !== null);

            if (items.length === 0) {
                event.preventDefault();
                focusDialog();

                return;
            }

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    modal.addEventListener('animationend', () => modal.classList.remove('is-fading-in'));

    window.addEventListener('popstate', (event) => {
        if (ignoreNextPop) {
            ignoreNextPop = false;
            window.setTimeout(flushPendingScroll, CLOSE_MS);

            return;
        }

        pendingScroll = null;
        pushedEntry = false;

        const url = event.state?.projectModal;

        if (url) {
            open(url, { push: false });
        } else {
            hideShell();
        }
    });

    // ----------------------------------------------------------
    //  Entrada directa a /proyectos/{slug}: el servidor ya pintó el detalle.
    // ----------------------------------------------------------
    if (isOpen) {
        modal.hidden = false;
        lockScroll(true);
        history.replaceState({ projectModal: window.location.href }, '', window.location.href);
        focusDialog();
    }
}
