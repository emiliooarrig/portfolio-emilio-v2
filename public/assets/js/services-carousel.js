/**
 * services-carousel.js — flechas, puntos y teclado del carrusel.
 *
 * El desplazamiento real lo hace el navegador: el riel es un contenedor con
 * scroll y scroll-snap. Aquí sólo se empuja ese scroll y se refleja en qué
 * tarjeta va. Si este módulo no corre, el carrusel se sigue deslizando con
 * dedo o trackpad y los controles quedan ocultos.
 *
 * No hay avance automático a propósito: la tarjeta se lee, no se mira pasar.
 * Las flechas sí son circulares: del último grupo se vuelve al primero.
 */

function setupCarousel(root) {
    const track = root.querySelector('[data-carousel-track]');
    const controls = root.querySelector('[data-carousel-controls]');

    if (!track || !controls) {
        return;
    }

    const slides = Array.from(track.children);

    if (slides.length === 0) {
        return;
    }

    const prev = controls.querySelector('[data-carousel-prev]');
    const next = controls.querySelector('[data-carousel-next]');
    const dots = Array.from(controls.querySelectorAll('[data-carousel-dot]'));

    controls.hidden = false;

    /** Desplazamiento del riel al que corresponde una tarjeta. */
    const offsetOf = (index) => slides[index].offsetLeft - track.offsetLeft;

    /** Índice de la tarjeta pegada al borde izquierdo del riel. */
    const currentIndex = () => {
        const left = track.scrollLeft;
        let closest = 0;
        let min = Infinity;

        slides.forEach((slide, index) => {
            const distance = Math.abs(slide.offsetLeft - track.offsetLeft - left);

            if (distance < min) {
                min = distance;
                closest = index;
            }
        });

        return closest;
    };

    /**
     * Índice al que se está animando, o `null` si el riel está quieto. Sin
     * esto, dos clics seguidos leerían el scroll a medio camino y la vuelta
     * se daría en el momento equivocado.
     */
    let pending = null;
    let pendingTimer = 0;

    const clearPending = () => {
        pending = null;
        window.clearTimeout(pendingTimer);
    };

    /**
     * @param {boolean} instant Sin animación: para el salto que cierra la vuelta.
     */
    const scrollToIndex = (index, instant = false) => {
        const target = Math.max(0, Math.min(index, slides.length - 1));

        if (!slides[target]) {
            return;
        }

        const left = offsetOf(target);

        // Sin `behavior` manda el CSS, que respeta prefers-reduced-motion.
        track.scrollTo(instant ? { left, behavior: 'auto' } : { left });

        if (instant) {
            clearPending();

            return;
        }

        pending = target;
        window.clearTimeout(pendingTimer);
        // Red de seguridad: si el usuario arrastra a media animación, la
        // intención caduca y el índice vuelve a leerse del riel.
        pendingTimer = window.setTimeout(clearPending, 700);
    };

    /** Cuántas tarjetas caben a la vista: el salto es de una página, no de una. */
    const perView = () => {
        const slideWidth = slides[0].getBoundingClientRect().width;

        return slideWidth > 0 ? Math.max(1, Math.round(track.clientWidth / slideWidth)) : 1;
    };

    /** Último índice que se puede pegar al borde sin dejar hueco al final. */
    const lastIndex = () => Math.max(0, slides.length - perView());

    /**
     * Avanza `step` tarjetas dando la vuelta: pasado el final vuelve al
     * principio y antes del principio salta al final.
     */
    const move = (step) => {
        const from = pending ?? currentIndex();
        const last = lastIndex();
        let to = from + step;

        if (to > last) {
            to = 0;
        } else if (to < 0) {
            to = last;
        }

        if (to === from) {
            return;
        }

        // La vuelta se da de golpe: animada, el riel desharía todo el
        // recorrido hacia atrás y se leería como un retroceso, no como un ciclo.
        const closingTheLoop = step > 0 ? to < from : to > from;

        scrollToIndex(to, closingTheLoop);
    };

    const sync = () => {
        const index = currentIndex();

        // Llegó a donde iba: la intención ya no hace falta.
        if (pending !== null && Math.abs(track.scrollLeft - offsetOf(pending)) <= 2) {
            clearPending();
        }

        // Girando, las flechas sólo se apagan si no hay nada que rotar.
        const canMove = slides.length > perView();

        dots.forEach((dot, dotIndex) => {
            dot.setAttribute('aria-selected', String(dotIndex === index));
        });

        if (prev) {
            prev.disabled = !canMove;
        }

        if (next) {
            next.disabled = !canMove;
        }
    };

    prev?.addEventListener('click', () => move(-perView()));
    next?.addEventListener('click', () => move(perView()));

    dots.forEach((dot) => {
        dot.addEventListener('click', () => scrollToIndex(Number(dot.dataset.carouselDot)));
    });

    // El riel es enfocable: con foco, las flechas del teclado avanzan de una,
    // también dando la vuelta.
    track.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
            return;
        }

        event.preventDefault();
        move(event.key === 'ArrowRight' ? 1 : -1);
    });

    // Un solo listener de scroll, agrupado en requestAnimationFrame.
    let ticking = false;

    track.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            sync();
        });
    }, { passive: true });

    window.addEventListener('resize', sync, { passive: true });
    sync();
}

export function initServicesCarousel() {
    document.querySelectorAll('[data-carousel]').forEach(setupCarousel);
}
