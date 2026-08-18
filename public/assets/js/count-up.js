/**
 * count-up.js — contadores que van de 0 al valor real al entrar en viewport.
 *
 * Es la animación más notoria del sitio junto con el modal: se reserva para
 * las métricas, que es donde el número *es* el mensaje.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

const DURATION = 1400;

/** Desaceleración: rápido al inicio, se posa en el valor final. */
function easeOutExpo(t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
}

function run(el) {
    const target = Number(el.dataset.countTo || '0');

    if (!Number.isFinite(target)) {
        return;
    }

    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - start) / DURATION, 1);
        const value = Math.round(easeOutExpo(progress) * target);

        el.textContent = String(value);

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
}

export function initCountUp() {
    const counters = Array.from(document.querySelectorAll('[data-count-to]'));

    if (counters.length === 0) {
        return;
    }

    // Sin animación: el número aparece ya escrito, no en cero.
    if (REDUCED_MOTION.matches || !('IntersectionObserver' in window)) {
        counters.forEach((el) => {
            el.textContent = el.dataset.countTo || '0';
        });

        return;
    }

    counters.forEach((el) => {
        el.textContent = '0';
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            run(entry.target);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => observer.observe(el));
}
