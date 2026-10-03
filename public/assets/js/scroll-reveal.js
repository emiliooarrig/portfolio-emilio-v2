/**
 * scroll-reveal.js — apariciones al hacer scroll y la página que se vuelve
 * cobalto en "Cómo trabajo".
 *
 * Cada [data-reveal] recibe .is-revealed una sola vez y deja de
 * observarse. El estado oculto sólo existe bajo `html.js` y fuera de
 * movimiento reducido (animations.scss): sin JS nada se oculta.
 */

export function initScrollReveal() {
    const items = document.querySelectorAll('[data-reveal]');

    if (!('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-revealed'));

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

    items.forEach((item) => observer.observe(item));

    // La página entera pasa a cobalto mientras la sección cruza el centro
    // de la pantalla; la sección ya es cobalto por sí misma sin JS.
    const trigger = document.querySelector('[data-surface-trigger]');

    if (trigger) {
        new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                document.body.dataset.surface = 'cobalt';
            } else {
                delete document.body.dataset.surface;
            }
        }, { rootMargin: '-45% 0px -45% 0px' }).observe(trigger);
    }
}
