/**
 * scroll-reveal.js — entrada de las celdas bento al cruzar el viewport.
 *
 * Sólo IntersectionObserver: cero listeners de scroll. El estado oculto lo
 * aplica el CSS bajo `html.js`, así que si este módulo no corre, nada queda
 * invisible.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');
const MOBILE = window.matchMedia('(max-width: 40rem)');

/** Tope de escalones: en móvil el retardo acumulado se nota como lentitud. */
function maxStagger() {
    return MOBILE.matches ? 2 : 7;
}

/**
 * Reparte el índice de escalonado entre los hijos de cada contenedor
 * `.reveal--stagger` y los marca como revelables.
 */
function prepareStagger(scope) {
    scope.querySelectorAll('.reveal--stagger').forEach((container) => {
        Array.from(container.children).forEach((child, index) => {
            child.classList.add('reveal');
            child.style.setProperty('--reveal-index', String(Math.min(index, maxStagger())));
        });
    });
}

/** Los chips llevan su propia cascada, más rápida que la de las celdas. */
function prepareChips(scope) {
    scope.querySelectorAll('.chip-list--animated').forEach((list) => {
        Array.from(list.children).forEach((chip, index) => {
            chip.style.setProperty('--chip-index', String(Math.min(index, 12)));
        });
    });
}

function revealAll(scope) {
    scope.querySelectorAll('.reveal, .chip-list--animated').forEach((el) => {
        el.classList.add('is-revealed');
    });
}

/**
 * Observa un ámbito (la página completa o el contenido recién inyectado
 * en el modal) y revela sus elementos al entrar en pantalla.
 */
export function observeReveals(scope = document) {
    prepareStagger(scope);
    prepareChips(scope);

    if (REDUCED_MOTION.matches || !('IntersectionObserver' in window)) {
        revealAll(scope);

        return;
    }

    const targets = scope.querySelectorAll('.reveal:not(.is-revealed), .chip-list--animated:not(.is-revealed)');

    if (targets.length === 0) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    targets.forEach((target) => observer.observe(target));

    // Red de seguridad: si el observer no dispara (reflow tardío por fuentes,
    // scroll restaurado por el navegador), nada se queda invisible.
    window.setTimeout(() => {
        targets.forEach((target) => {
            if (target.getBoundingClientRect().top < window.innerHeight) {
                target.classList.add('is-revealed');
            }
        });
    }, 1600);
}

export function initScrollReveal() {
    observeReveals(document);
}
