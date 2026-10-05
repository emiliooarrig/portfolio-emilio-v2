/**
 * cursor.js — cursor contextual y relleno direccional de las filas. Sólo
 * con puntero fino; se mueve en el bucle de loop.js, que se detiene en
 * reposo, con la pestaña oculta o fuera de la ventana.
 */

import { animate } from './loop.js';

const FIELDS = 'input, textarea, select, [contenteditable], dialog[open]';
const LINKS = 'a, button, label, summary, [role="button"]';

/** El relleno de cada fila entra y sale por el borde que cruza el puntero. */
function initRows() {
    document.querySelectorAll('.project-row').forEach((row) => {
        const side = (event) => {
            const rect = row.getBoundingClientRect();

            row.dataset.dir = event.clientY < rect.top + rect.height / 2 ? 'top' : 'bottom';
        };

        row.addEventListener('pointerenter', (event) => {
            if (event.pointerType !== 'mouse' && event.pointerType !== 'pen') {
                return;
            }

            side(event);
            row.classList.add('is-hovered');
        });

        row.addEventListener('pointerleave', (event) => {
            side(event);
            row.classList.remove('is-hovered');
        });
    });
}

export function initCursor() {
    if (!matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    initRows();

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ease = reduced ? 1 : 0.18;

    const cursor = document.createElement('div');

    cursor.className = 'cursor is-off';
    cursor.setAttribute('aria-hidden', 'true');
    cursor.innerHTML = '<div class="cursor__press"><div class="cursor__dot"></div><div class="cursor__shape"><span class="cursor__label"></span></div></div>';
    document.body.appendChild(cursor);
    document.documentElement.classList.add('has-custom-cursor');

    const label = cursor.querySelector('.cursor__label');

    const pointer = { x: -100, y: -100 };
    const dot = { x: -100, y: -100 };
    let inside = false;
    let running = false;

    const step = () => {
        dot.x += (pointer.x - dot.x) * ease;
        dot.y += (pointer.y - dot.y) * ease;
        cursor.style.transform = `translate3d(${dot.x}px, ${dot.y}px, 0)`;

        const moving = Math.abs(pointer.x - dot.x) + Math.abs(pointer.y - dot.y) > 0.1;

        running = moving && inside;

        return running;
    };

    const wake = () => {
        if (!running) {
            running = true;
            animate(step);
        }
    };

    const update = (target) => {
        let state = 'rest';
        let text = '';

        if (target.closest(FIELDS)) {
            state = 'hidden';
        } else if (target.closest('[data-cursor]')) {
            state = 'label';
            text = target.closest('[data-cursor]').dataset.cursor;
        } else if (target.closest(LINKS)) {
            state = 'link';
        }

        if (cursor.dataset.state !== state) {
            cursor.dataset.state = state;
        }

        if (text && label.textContent !== text) {
            label.textContent = text;
        }

        cursor.classList.toggle('is-inverted', Boolean(target.closest('.project-row.is-hovered, .section--cobalt')));
    };

    document.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' && event.pointerType !== 'pen') {
            return;
        }

        pointer.x = event.clientX;
        pointer.y = event.clientY;

        if (!inside) {
            inside = true;
            dot.x = pointer.x;
            dot.y = pointer.y;
            cursor.classList.remove('is-off');
        }

        update(event.target);
        wake();
    }, { passive: true });

    // El contenido bajo el puntero cambia al hacer scroll aunque no se mueva.
    window.addEventListener('scroll', () => {
        if (inside) {
            const target = document.elementFromPoint(pointer.x, pointer.y);

            if (target) {
                update(target);
            }
        }
    }, { passive: true });

    document.documentElement.addEventListener('pointerleave', () => {
        inside = false;
        cursor.classList.add('is-off');
    });

    document.addEventListener('pointerdown', () => cursor.classList.add('is-pressed'));
    document.addEventListener('pointerup', () => cursor.classList.remove('is-pressed'));
}
