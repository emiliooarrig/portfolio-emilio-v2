/**
 * project-modal.js — detalle de proyecto como overlay sobre la landing.
 *
 * La página nunca se recarga: el fragmento llega por fetch y la URL se
 * actualiza con history.pushState sólo para que el enlace sea compartible.
 * Sin JS, la tarjeta sigue siendo un enlace normal a /proyectos/{slug}, que el
 * servidor responde con la landing completa y el modal ya abierto.
 */

import { observeReveals } from './scroll-reveal.js';

const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'textarea',
    'input:not([type="hidden"])',
    'select',
    '[tabindex]:not([tabindex="-1"])',
].join(', ');

const CLOSE_MS = 240;

const LOADING_HTML = `
    <p class="project-modal__loading meta">
        <span class="project-modal__spinner" aria-hidden="true"></span>
        Cargando proyecto…
    </p>`;

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

    // ----------------------------------------------------------
    //  Apertura / cierre
    // ----------------------------------------------------------
    const lockScroll = (locked) => document.body.classList.toggle('is-locked', locked);

    const focusDialog = () => {
        if (dialog) {
            dialog.focus({ preventScroll: true });
        }
    };

    const showShell = () => {
        window.clearTimeout(closeTimer);
        modal.classList.remove('is-closing');
        modal.hidden = false;
        lockScroll(true);
        isOpen = true;
    };

    /** Cierre visual puro: no toca el historial. */
    const hideShell = () => {
        if (!isOpen) {
            return;
        }

        isOpen = false;
        modal.classList.add('is-closing');

        closeTimer = window.setTimeout(() => {
            modal.hidden = true;
            modal.classList.remove('is-closing');
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
        }, CLOSE_MS);
    };

    async function load(url) {
        content.innerHTML = LOADING_HTML;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            content.innerHTML = await response.text();
            content.scrollTop = 0;
            observeReveals(content);
            focusDialog();
        } catch (error) {
            // Si el fragmento no llega, el enlace directo sigue siendo válido.
            content.innerHTML = `
                <p class="empty-state">
                    No se pudo cargar el proyecto.
                    <a href="${url}">Abrirlo en su propia página</a>.
                </p>`;
        }
    }

    function open(url, { push = true } = {}) {
        if (!isOpen) {
            lastFocus = document.activeElement;
        }

        showShell();

        if (push) {
            history.pushState({ projectModal: url }, '', url);
            pushedEntry = true;
        }

        load(url);
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
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
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

            return;
        }

        // Tarjeta completa clickeable: el clic fuera de un enlace también abre.
        const card = event.target.closest('[data-project-card]');

        if (card && !event.target.closest('a')) {
            if (window.getSelection()?.toString()) {
                return;
            }

            const link = card.querySelector('[data-project-link]');

            if (link) {
                event.preventDefault();
                open(link.getAttribute('href'));
            }
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

    window.addEventListener('popstate', (event) => {
        if (ignoreNextPop) {
            ignoreNextPop = false;
            window.setTimeout(flushPendingScroll, CLOSE_MS);

            return;
        }

        pendingScroll = null;

        const url = event.state?.projectModal;

        if (url) {
            pushedEntry = false;
            open(url, { push: false });
        } else {
            pushedEntry = false;
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
        observeReveals(content);
        focusDialog();
    }
}
