/**
 * hero-brush.js — el ámbar del nombre sigue al puntero como un pincel.
 *
 * El efecto vive en `_hero.scss`: cada palabra tiene encima una copia de sí
 * misma en ámbar que una máscara de círculos va destapando. Aquí sólo se
 * dice *dónde* está el pincel, en píxeles relativos a cada palabra.
 *
 * Se guardan las últimas posiciones porque el trazo no es un punto: al mover
 * rápido, esas muestras quedan separadas y la máscara se estira en una
 * pincelada; al frenar, se juntan otra vez en la punta redonda. La posición
 * se persigue con una interpolación suave (como el anillo del cursor) para
 * que el trazo tenga peso en vez de saltar de frame en frame.
 *
 * El bucle de rAF sólo corre mientras el pincel se está acercando: quieto
 * sobre una letra no gasta frames, y fuera del nombre no corre nada.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

/** Muestras de la estela. Debe coincidir con $brush-trail en _hero.scss. */
const SAMPLES = 6;

/** Cuánto se acerca el pincel al puntero en cada frame (0-1). */
const EASING = 0.34;

/** Por debajo de esto el trazo ya alcanzó al puntero y el bucle se para. */
const SETTLED = 0.15;

export function initHeroBrush() {
    // Sin puntero fino no hay hover: en táctil el nombre se queda como está.
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    const name = document.querySelector('[data-hero-brush]');
    const words = name ? [...name.querySelectorAll('.hero__word')] : [];

    if (words.length === 0) {
        return;
    }

    // Puntero (destino), pincel (posición perseguida) y su rastro reciente.
    let pointerX = 0;
    let pointerY = 0;
    let brushX = 0;
    let brushY = 0;
    let running = false;

    const trail = Array.from({ length: SAMPLES }, () => ({ x: 0, y: 0 }));

    const paint = () => {
        // Primero se miden todas las palabras y después se escribe: leer y
        // escribir en el mismo bucle obligaría al navegador a recalcular
        // layout una vez por palabra.
        const boxes = words.map((word) => word.getBoundingClientRect());
        const flat = REDUCED_MOTION.matches;

        words.forEach((word, index) => {
            const box = boxes[index];

            for (let sample = 0; sample < SAMPLES; sample += 1) {
                // Sin movimiento no hay estela: todas las muestras al día.
                const point = flat ? trail[0] : trail[sample];

                word.style.setProperty(`--brush-x${sample}`, `${(point.x - box.left).toFixed(1)}px`);
                word.style.setProperty(`--brush-y${sample}`, `${(point.y - box.top).toFixed(1)}px`);
            }
        });
    };

    const loop = () => {
        const dx = pointerX - brushX;
        const dy = pointerY - brushY;

        // Sin animación el pincel no se arrastra: va pegado al puntero.
        const easing = REDUCED_MOTION.matches ? 1 : EASING;

        brushX += dx * easing;
        brushY += dy * easing;

        trail.pop();
        trail.unshift({ x: brushX, y: brushY });

        paint();

        // El bucle sigue hasta que el pincel alcanza al puntero y la estela
        // termina de recogerse sobre él.
        const tail = trail[SAMPLES - 1];
        const settled =
            Math.abs(dx) < SETTLED &&
            Math.abs(dy) < SETTLED &&
            Math.abs(tail.x - brushX) < SETTLED &&
            Math.abs(tail.y - brushY) < SETTLED;

        if (settled) {
            running = false;

            return;
        }

        requestAnimationFrame(loop);
    };

    const start = () => {
        if (running) {
            return;
        }

        running = true;
        requestAnimationFrame(loop);
    };

    const place = (event) => {
        pointerX = event.clientX;
        pointerY = event.clientY;
    };

    name.addEventListener('pointerenter', (event) => {
        if (event.pointerType === 'touch') {
            return;
        }

        place(event);

        // El pincel aparece donde entró el puntero, no arrastrando un trazo
        // desde donde se quedó la última vez.
        brushX = pointerX;
        brushY = pointerY;
        trail.forEach((point) => {
            point.x = brushX;
            point.y = brushY;
        });

        paint();
        name.classList.add('is-brushing');
    }, { passive: true });

    name.addEventListener('pointermove', (event) => {
        if (event.pointerType === 'touch') {
            return;
        }

        place(event);
        start();
    }, { passive: true });

    // Al salir el trazo se queda donde estaba mientras se desvanece: el bucle
    // ya no tiene nada que perseguir.
    name.addEventListener('pointerleave', () => {
        name.classList.remove('is-brushing');
    }, { passive: true });
}
