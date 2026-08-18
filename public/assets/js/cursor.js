/**
 * cursor.js — cursor propio: punto inmediato + anillo que llega un paso después.
 *
 * Sólo se activa con puntero fino. En táctil o con `pointer: coarse` no se
 * construye nada y el cursor del sistema queda intacto. El bucle de rAF sólo
 * corre mientras el anillo aún se está acercando: en reposo no gasta frames.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

/** Qué se considera interactivo para agrandar el anillo. */
const INTERACTIVE = 'a, button, [role="button"], [data-project-card], label, summary';
const TEXT_FIELD = 'input, textarea, select';

/** Cuánto se acerca el anillo al puntero en cada frame (0-1). */
const EASING = 0.18;

export function initCursor() {
    const fine = window.matchMedia('(hover: hover) and (pointer: fine)');

    if (!fine.matches) {
        return;
    }

    const root = document.createElement('div');
    root.className = 'cursor';
    root.setAttribute('aria-hidden', 'true');
    root.innerHTML = '<span class="cursor__ring"></span><span class="cursor__dot"></span>';
    document.body.appendChild(root);

    const ring = root.querySelector('.cursor__ring');
    const dot = root.querySelector('.cursor__dot');

    document.documentElement.classList.add('has-cursor');

    // Posición real del puntero y posición (retrasada) del anillo.
    let pointerX = window.innerWidth / 2;
    let pointerY = window.innerHeight / 2;
    let ringX = pointerX;
    let ringY = pointerY;
    let running = false;

    const place = (el, x, y) => {
        el.style.setProperty('--x', `${x}px`);
        el.style.setProperty('--y', `${y}px`);
    };

    const loop = () => {
        const dx = pointerX - ringX;
        const dy = pointerY - ringY;

        // Suficientemente cerca: se posa en el destino y el bucle se detiene.
        if (Math.abs(dx) < 0.1 && Math.abs(dy) < 0.1) {
            ringX = pointerX;
            ringY = pointerY;
            place(ring, ringX, ringY);
            running = false;

            return;
        }

        ringX += dx * EASING;
        ringY += dy * EASING;
        place(ring, ringX, ringY);

        requestAnimationFrame(loop);
    };

    const start = () => {
        if (running) {
            return;
        }

        running = true;
        requestAnimationFrame(loop);
    };

    document.addEventListener('pointermove', (event) => {
        // Un lápiz o un dedo no deben encender el cursor del ratón.
        if (event.pointerType !== 'mouse') {
            return;
        }

        const first = !root.classList.contains('is-active');

        pointerX = event.clientX;
        pointerY = event.clientY;

        place(dot, pointerX, pointerY);
        root.classList.add('is-active');

        // Sin animación el anillo no se arrastra: va pegado al punto.
        // En el primer movimiento también se coloca de golpe, para que nunca
        // aparezca en el origen si rAF llega tarde (pestaña en segundo plano).
        if (REDUCED_MOTION.matches || first) {
            ringX = pointerX;
            ringY = pointerY;
            place(ring, ringX, ringY);
        }

        if (!REDUCED_MOTION.matches) {
            start();
        }

        const target = event.target;
        const overText = target instanceof Element && target.closest(TEXT_FIELD) !== null;
        const overLink = target instanceof Element && target.closest(INTERACTIVE) !== null;

        root.classList.toggle('is-text', overText);
        root.classList.toggle('is-hovering', overLink && !overText);
    }, { passive: true });

    document.addEventListener('pointerdown', () => root.classList.add('is-pressed'));
    document.addEventListener('pointerup', () => root.classList.remove('is-pressed'));

    // Fuera de la ventana el cursor propio se apaga: el del sistema toma el relevo.
    document.addEventListener('pointerleave', () => root.classList.remove('is-active'));
    window.addEventListener('blur', () => root.classList.remove('is-active', 'is-pressed'));

    // Si el usuario cambia a táctil (híbridos, tablet con teclado), se retira.
    fine.addEventListener('change', (event) => {
        if (!event.matches) {
            document.documentElement.classList.remove('has-cursor');
            root.remove();
        }
    });
}
