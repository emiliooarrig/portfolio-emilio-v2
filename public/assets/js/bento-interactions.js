/**
 * bento-interactions.js — comportamiento de las celdas del grid.
 *
 * Nota: el hover y el foco viven en CSS. Aquí sólo va lo que el CSS
 * no puede hacer: entrada escalonada de las tarjetas al aparecer.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

/**
 * Aparición en cascada de las tarjetas al entrar en pantalla.
 *
 * Sólo se ocultan las tarjetas que nacen por debajo del pliegue: lo que ya
 * está visible al cargar nunca se toca, así ninguna falla del observer puede
 * dejar contenido invisible.
 */
function initRevealOnScroll() {
    const cards = Array.from(document.querySelectorAll('.bento-card, .cert-card, .timeline__item'));

    if (cards.length === 0 || REDUCED_MOTION.matches || !('IntersectionObserver' in window)) {
        return;
    }

    const reveal = (card, delay = 0) => {
        if (card.dataset.revealed === 'true') {
            return;
        }

        card.dataset.revealed = 'true';
        card.style.transition = `opacity 320ms cubic-bezier(0.23, 1, 0.32, 1) ${delay}ms, transform 320ms cubic-bezier(0.23, 1, 0.32, 1) ${delay}ms`;
        card.style.opacity = '1';
        card.style.transform = 'none';
    };

    const hidden = cards.filter((card) => card.getBoundingClientRect().top > window.innerHeight * 0.9);

    if (hidden.length === 0) {
        return;
    }

    hidden.forEach((card) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (!entry.isIntersecting) {
                return;
            }

            reveal(entry.target, Math.min(index * 50, 200));
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

    hidden.forEach((card) => observer.observe(card));

    // Red de seguridad: si el observer no dispara (reflow tardío por fuentes,
    // navegador con scroll restaurado), nada se queda invisible.
    window.setTimeout(() => hidden.forEach((card) => {
        if (card.getBoundingClientRect().top < window.innerHeight) {
            reveal(card);
        }
    }), 1500);
}

/**
 * Las tarjetas de proyecto son clickeables completas: un clic sobre la
 * celda (no sobre un enlace interno) dispara el enlace principal.
 */
function initCardActivation() {
    document.querySelectorAll('.bento-card').forEach((card) => {
        const link = card.querySelector('.bento-card__link');

        if (!link) {
            return;
        }

        card.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                return;
            }

            // Respeta la selección de texto.
            if (window.getSelection()?.toString()) {
                return;
            }

            link.click();
        });
    });
}

export function initBentoInteractions() {
    initRevealOnScroll();
    initCardActivation();
}
