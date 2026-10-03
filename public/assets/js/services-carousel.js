/**
 * services-carousel.js — flechas, puntos y teclado del carrusel.
 *
 * El desplazamiento real lo hace el navegador: el riel es un contenedor con
 * scroll y scroll-snap. Aquí sólo se empuja ese scroll y se refleja en qué
 * tarjeta va. Si este módulo no corre, el carrusel se sigue deslizando con
 * dedo o trackpad y los controles quedan ocultos.
 *
 * No hay avance automático a propósito: la tarjeta se lee, no se mira pasar.
 *
 * El giro es de 360°: detrás de la última tarjeta vuelve a venir la primera y
 * se sigue avanzando hacia el mismo lado. Para lograrlo el riel lleva tres
 * copias de las tarjetas y el carrusel descansa siempre en la de en medio; al
 * acercarse a un extremo se reubica una copia entera de golpe. Como la copia a
 * la que llega es idéntica a la que deja, ese salto no se ve: lo único que se
 * percibe es que el riel nunca se acaba ni rebota hacia atrás.
 */

/** Copias del riel: la real más una a cada lado para tener pista de sobra. */
const COPIES = 3;

/** Silencio tras el último evento de scroll para dar el riel por quieto. */
const IDLE = 140;

function setupCarousel(root) {
    const track = root.querySelector('[data-carousel-track]');
    const controls = root.querySelector('[data-carousel-controls]');

    if (!track || !controls) {
        return;
    }

    const originals = Array.from(track.children);
    const count = originals.length;

    if (count === 0) {
        return;
    }

    const prev = controls.querySelector('[data-carousel-prev]');
    const next = controls.querySelector('[data-carousel-next]');
    const dots = Array.from(controls.querySelectorAll('[data-carousel-dot]'));

    controls.hidden = false;

    /** Los duplicados que dan la vuelta. Vacío mientras no hacen falta. */
    let clones = [];

    const looping = () => clones.length > 0;

    /** Desplazamiento del riel al que corresponde una tarjeta. */
    const offsetOf = (index) => {
        const slide = track.children[index];

        return slide ? slide.offsetLeft - track.offsetLeft : 0;
    };

    /** Índice de la tarjeta pegada al borde izquierdo del riel. */
    const currentIndex = () => {
        const left = track.scrollLeft;
        let closest = 0;
        let min = Infinity;

        Array.from(track.children).forEach((slide, index) => {
            const distance = Math.abs(slide.offsetLeft - track.offsetLeft - left);

            if (distance < min) {
                min = distance;
                closest = index;
            }
        });

        return closest;
    };

    /** Cuántas tarjetas caben a la vista: el salto es de una página, no de una. */
    const perView = () => {
        const slideWidth = originals[0].getBoundingClientRect().width;

        return slideWidth > 0 ? Math.max(1, Math.round(track.clientWidth / slideWidth)) : 1;
    };

    /** Ancho de una copia completa: lo que mide reubicarse una vuelta. */
    const loopWidth = () => (looping() ? offsetOf(count) - offsetOf(0) : 0);

    /**
     * Último índice que se puede pegar al borde izquierdo. Se lee del scroll
     * máximo real y no de una cuenta de tarjetas: así el hueco que deja el
     * final del riel no descuadra el cálculo.
     */
    const lastAnchorable = () => {
        const max = track.scrollWidth - track.clientWidth;
        let last = 0;

        Array.from(track.children).forEach((slide, index) => {
            if (slide.offsetLeft - track.offsetLeft <= max + 1) {
                last = index;
            }
        });

        return last;
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
     * @param {boolean} instant Sin animación: para los saltos entre copias.
     */
    const scrollToIndex = (index, instant = false) => {
        const target = Math.max(0, Math.min(index, track.children.length - 1));

        if (!track.children[target]) {
            return;
        }

        const left = offsetOf(target);

        // Sin `behavior` manda el CSS, que respeta prefers-reduced-motion.
        // Para el salto hace falta 'instant': 'auto' delega en el CSS, que
        // aquí es `scroll-behavior: smooth`, y el brinco entre copias se
        // vería como el retroceso que queremos evitar.
        track.scrollTo(instant ? { left, behavior: 'instant' } : { left });

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

    /** Reubica el riel `copies` copias sin animación y sin mover lo que se ve. */
    const shiftCopies = (copies) => {
        const width = loopWidth();

        if (copies === 0 || width <= 0) {
            return;
        }

        track.scrollTo({ left: track.scrollLeft + copies * width, behavior: 'instant' });
        // Lectura forzada: el salto tiene que quedar aplicado antes de que
        // salga la animación siguiente, o el navegador la arrancaría desde
        // la posición vieja y se vería el retroceso que queremos evitar.
        void track.scrollLeft;
    };

    /**
     * Devuelve el índice equivalente a `to` que sí se puede anclar, moviendo
     * el riel a la copia que haga falta. Es lo que convierte el final del
     * carrusel en una vuelta y no en un rebote.
     */
    const rebase = (to) => {
        if (!looping()) {
            return Math.max(0, Math.min(to, lastAnchorable()));
        }

        const last = lastAnchorable();
        let target = to;
        let copies = 0;

        // Los topes son sólo un seguro: una vuelta basta para que cualquier
        // destino quepa en el riel.
        while (target > last && copies > -COPIES) {
            target -= count;
            copies -= 1;
        }

        while (target < 0 && copies < COPIES) {
            target += count;
            copies += 1;
        }

        shiftCopies(copies);

        return Math.max(0, Math.min(target, last));
    };

    /** Deja el riel descansando en la copia de en medio. */
    const recenter = () => {
        if (!looping() || pending !== null) {
            return;
        }

        const copy = Math.floor(currentIndex() / count);

        if (copy !== 1) {
            shiftCopies(1 - copy);
        }
    };

    /**
     * Monta o desmonta las copias según quepan o no todas las tarjetas: si el
     * riel ya las muestra todas no hay vuelta que dar y los duplicados sólo
     * se verían repetidos en pantalla.
     */
    const syncClones = () => {
        const wanted = count > perView();

        if (wanted === looping()) {
            return;
        }

        const real = currentIndex() % count;

        if (wanted) {
            for (let copy = 1; copy < COPIES; copy += 1) {
                originals.forEach((slide) => {
                    const clone = slide.cloneNode(true);

                    // Copia decorativa: ni la lee un lector de pantalla ni
                    // recibe foco, para no duplicar las tarjetas reales.
                    clone.setAttribute('aria-hidden', 'true');
                    clone.setAttribute('inert', '');
                    clone.dataset.carouselClone = '';
                    track.appendChild(clone);
                    clones.push(clone);
                });
            }

            // Arranca en la copia de en medio: así la primera tarjeta también
            // tiene recorrido hacia atrás desde el primer clic.
            scrollToIndex(count + real, true);

            return;
        }

        clones.forEach((clone) => clone.remove());
        clones = [];
        scrollToIndex(real, true);
    };

    /**
     * Avanza `step` tarjetas. Pasado el final sigue hacia el mismo lado
     * porque la copia siguiente vuelve a empezar por la primera tarjeta.
     */
    const move = (step) => {
        const from = pending ?? currentIndex();
        const to = rebase(from + step);

        if (to === currentIndex()) {
            return;
        }

        scrollToIndex(to);
    };

    const sync = () => {
        // Llegó a donde iba: la intención ya no hace falta.
        if (pending !== null && Math.abs(track.scrollLeft - offsetOf(pending)) <= 2) {
            clearPending();
        }

        const index = currentIndex() % count;

        // Girando, las flechas sólo se apagan si no hay nada que rotar.
        const canMove = count > perView();

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
        dot.addEventListener('click', () => {
            const copy = looping() ? Math.floor(currentIndex() / count) : 0;

            scrollToIndex(rebase(copy * count + Number(dot.dataset.carouselDot)));
        });
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

    // Un solo listener de scroll, agrupado en requestAnimationFrame. Cuando
    // el riel se queda quieto se vuelve a la copia de en medio, que es lo que
    // deja pista para seguir deslizando con el dedo hacia cualquier lado.
    let ticking = false;
    let idleTimer = 0;

    track.addEventListener('scroll', () => {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(() => {
            recenter();
            sync();
        }, IDLE);

        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            sync();
        });
    }, { passive: true });

    window.addEventListener('resize', () => {
        syncClones();
        sync();
    }, { passive: true });

    syncClones();
    sync();
}

export function initServicesCarousel() {
    document.querySelectorAll('[data-carousel]').forEach(setupCarousel);
}
