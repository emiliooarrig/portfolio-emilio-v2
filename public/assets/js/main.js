/**
 * main.js — arranque del front-end.
 * Módulos ES nativos, sin dependencias.
 */

import { initBentoInteractions } from './bento-interactions.js';
import { initPipeline } from './pipeline.js';

/**
 * Menú de navegación en móvil.
 */
function initNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.getElementById('nav-menu');

    if (!toggle || !menu) {
        return;
    }

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        menu.classList.toggle('is-open', open);
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    // Cerrar al navegar o al presionar Escape.
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    // Al volver a escritorio el menú deja de ser un panel.
    window.matchMedia('(min-width: 60rem)').addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initBentoInteractions();
    initPipeline();
});
