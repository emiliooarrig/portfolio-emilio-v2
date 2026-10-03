/**
 * project-modal.js — detalle de proyecto como capa sobre la landing.
 *
 * El fragmento llega por fetch y la URL cambia con pushState sólo para que
 * el enlace sea compartible; sin JS, /proyectos/{slug} devuelve la landing
 * con el modal ya abierto. Con View Transitions el título de la fila viaja
 * al detalle y de vuelta (`project-title`, nunca en los dos a la vez); sin
 * la API, fundido.
 */

const FOCUSABLE = 'a[href], button:not([disabled]), textarea, input:not([type="hidden"]), select, [tabindex]:not([tabindex="-1"])';
const NAME = 'project-title';
const CLOSE_MS = 200;
const LOADING = '<p class="project-modal__loading meta">Cargando proyecto…</p>';
const REDUCED = matchMedia('(prefers-reduced-motion: reduce)');

const canMorph = () => typeof document.startViewTransition === 'function' && !REDUCED.matches;

/** Título de la fila que enlaza a esa URL, si está a la vista. */
function rowTitleFor(url) {
    const path = new URL(url, location.href).pathname;
    const link = [...document.querySelectorAll('[data-project-link]')].find((el) => new URL(el.href).pathname === path);
    const title = link?.closest('[data-project-card]')?.querySelector('.project-row__title');
    const rect = title?.getBoundingClientRect();

    return rect && rect.bottom > 0 && rect.top < innerHeight ? title : null;
}

async function fetchDetail(url) {
    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

        if (!response.ok) {
            throw new Error(response.status);
        }

        return await response.text();
    } catch {
        return `<p class="empty-state">No se pudo cargar el proyecto. <a href="${url}">Abrirlo en su propia página</a>.</p>`;
    }
}

export function initProjectModal() {
    const modal = document.querySelector('[data-project-modal]');

    if (!modal) {
        return;
    }

    const dialog = modal.querySelector('[data-modal-dialog]');
    const content = modal.querySelector('[data-modal-body]');
    const projectsUrl = `${document.body.dataset.base || ''}/#proyectos`;
    const siteTitle = document.body.dataset.siteTitle || '';

    let isOpen = modal.dataset.modalInitial === 'open';
    let currentUrl = isOpen ? location.href : null;
    let pushed = false;        // ¿esta apertura añadió una entrada al historial?
    let ignorePop = false;     // el propio cierre disparó el popstate
    let pendingScroll = null;  // ancla a la que ir una vez cerrado
    let lastFocus = null;
    let closeTimer = 0;
    let seq = 0;               // descarta respuestas que llegan tarde

    const lock = (on) => document.body.classList.toggle('is-locked', on);
    const focusDialog = () => dialog?.focus({ preventScroll: true });
    const detailTitle = () => content.querySelector('[data-detail-title]');

    const showShell = (fade = false) => {
        clearTimeout(closeTimer);
        modal.classList.remove('is-closing');
        modal.classList.toggle('is-fading-in', fade);
        modal.hidden = false;
        lock(true);
        isOpen = true;
    };

    const render = (html) => {
        content.innerHTML = html;
        content.scrollTop = 0;
        focusDialog();
    };

    const finishClose = () => {
        modal.hidden = true;
        modal.classList.remove('is-closing', 'is-fading-in');
        content.innerHTML = '';
        lock(false);

        if (siteTitle) {
            document.title = siteTitle;
        }

        if (lastFocus && document.contains(lastFocus)) {
            lastFocus.focus({ preventScroll: true });
        }

        lastFocus = null;
    };

    async function open(url, push = true) {
        const mine = ++seq;

        currentUrl = url;

        if (!isOpen) {
            lastFocus = document.activeElement;
        }

        if (push) {
            history.pushState({ projectModal: url }, '', url);
            pushed = true;
        }

        // Ya abierto o sin la API: la capa aparece ya, el contenido después.
        if (isOpen || !canMorph()) {
            showShell(!isOpen && !REDUCED.matches);
            content.innerHTML = LOADING;

            const html = await fetchDetail(url);

            if (mine === seq && isOpen) {
                render(html);
            }

            return;
        }

        // Con View Transitions se espera al contenido: el título tiene que
        // existir en los dos estados para viajar de uno a otro.
        const html = await fetchDetail(url);

        if (mine !== seq) {
            return;
        }

        const origin = rowTitleFor(url);

        if (origin) {
            origin.style.viewTransitionName = NAME;
        }

        document.startViewTransition(() => {
            if (origin) {
                origin.style.viewTransitionName = '';
            }

            showShell();
            render(html);

            if (origin && detailTitle()) {
                detailTitle().style.viewTransitionName = NAME;
            }
        });
    }

    /** Cierre visual; no toca el historial. */
    function hideShell() {
        if (!isOpen) {
            return;
        }

        isOpen = false;
        seq++;

        if (canMorph()) {
            // Con la URL del proyecto: en un "atrás" la barra ya muestra otra.
            const origin = currentUrl ? rowTitleFor(currentUrl) : null;
            const title = detailTitle();

            if (title) {
                title.style.viewTransitionName = origin ? NAME : '';
            }

            document.startViewTransition(() => {
                finishClose();

                if (origin) {
                    origin.style.viewTransitionName = NAME;
                }
            }).finished.finally(() => origin && (origin.style.viewTransitionName = ''));

            return;
        }

        if (REDUCED.matches) {
            finishClose();

            return;
        }

        modal.classList.add('is-closing');
        closeTimer = setTimeout(finishClose, CLOSE_MS);
    }

    /** Salta al ancla pedida cuando el modal ya soltó el scroll. */
    function flushPendingScroll() {
        const target = pendingScroll && document.querySelector(pendingScroll);

        pendingScroll = null;

        if (target) {
            requestAnimationFrame(() => requestAnimationFrame(() => {
                target.scrollIntoView({ behavior: REDUCED.matches ? 'auto' : 'smooth', block: 'start' });
            }));
        }
    }

    function requestClose() {
        if (!isOpen) {
            return;
        }

        hideShell();

        if (pushed) {
            // history.back() restaura la posición guardada: hay que esperarla.
            pushed = false;
            ignorePop = true;
            history.back();
        } else {
            history.replaceState({}, '', projectsUrl);
            setTimeout(flushPendingScroll, CLOSE_MS);
        }
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-project-link]');

        if (trigger && !event.metaKey && !event.ctrlKey && !event.shiftKey && event.button === 0) {
            event.preventDefault();
            open(trigger.getAttribute('href'));
        }
    });

    modal.addEventListener('click', (event) => {
        const jump = event.target.closest('[data-modal-scroll]');

        if (jump || event.target.closest('[data-modal-dismiss]')) {
            event.preventDefault();
            pendingScroll = jump ? jump.dataset.modalScroll : null;
            requestClose();
        }
    });

    modal.addEventListener('animationend', () => modal.classList.remove('is-fading-in'));

    document.addEventListener('keydown', (event) => {
        if (!isOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            requestClose();

            return;
        }

        // Trampa de foco: el tabulador no sale del diálogo.
        if (event.key === 'Tab' && dialog) {
            const items = [...dialog.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
            const first = items[0];
            const last = items[items.length - 1];

            if (!first) {
                event.preventDefault();
                focusDialog();
            } else if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.addEventListener('popstate', (event) => {
        if (ignorePop) {
            ignorePop = false;
            setTimeout(flushPendingScroll, CLOSE_MS);

            return;
        }

        pendingScroll = null;
        pushed = false;

        if (event.state?.projectModal) {
            open(event.state.projectModal, false);
        } else {
            hideShell();
        }
    });

    // Entrada directa a /proyectos/{slug}: el servidor ya pintó el detalle.
    if (isOpen) {
        modal.hidden = false;
        lock(true);
        history.replaceState({ projectModal: location.href }, '', location.href);
        focusDialog();
    }
}
