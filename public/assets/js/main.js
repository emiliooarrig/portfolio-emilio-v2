/**
 * main.js — arranque del front-end de la landing.
 * Módulos ES nativos, sin dependencias.
 */

import { initScrollSpy } from './scroll-spy.js';
import { initProjectModal } from './project-modal.js';
import { initAlerts } from './alerts.js';
import { initTheme } from './theme.js';

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

    // Al saltar a una sección el panel se cierra solo.
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

/**
 * El nombre del hero se asienta: cada palabra pasa de un ancho condensado
 * a su ancho final, una sola vez, al cargar. Es el único momento de
 * movimiento del sitio (ver `_hero.scss`).
 *
 * Se espera a Hubot Sans para no animar la fuente de respaldo; si no llega
 * a tiempo, se muestra el estado final sin animar.
 */
function initHeroName() {
    const name = document.querySelector('[data-hero-name]');

    if (!name) {
        return;
    }

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduce || !document.fonts) {
        name.classList.add('is-set');

        return;
    }

    const timeout = new Promise((resolve) => setTimeout(resolve, 1200, 'timeout'));

    Promise.race([document.fonts.load('800 1em "Hubot Sans"'), timeout])
        .then((result) => {
            name.classList.add(result === 'timeout' ? 'is-set' : 'is-animating');
        })
        .catch(() => name.classList.add('is-set'));
}

/**
 * La regla bajo el nav aparece al despegarse del hero.
 *
 * Un único listener de scroll pasivo, agrupado en requestAnimationFrame:
 * nunca se lee ni se escribe layout dentro del propio evento.
 */
function initScrollChrome() {
    const nav = document.querySelector('[data-nav]');

    if (!nav) {
        return;
    }

    let ticking = false;

    const update = () => {
        ticking = false;
        nav.classList.toggle('is-scrolled', window.scrollY > 8);
    };

    window.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(update);
    }, { passive: true });

    update();
}

/**
 * Enlaces internos: scroll suave con respaldo en JS y sin ensuciar el
 * historial con un hash por cada clic.
 */
function initAnchors() {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="#"], a[data-anchor]');

        if (!link || link.hasAttribute('data-modal-scroll')) {
            return;
        }

        const hash = (link.getAttribute('href') || '').replace(/^.*(?=#)/, '');

        if (!hash || hash === '#') {
            return;
        }

        const target = document.querySelector(hash);

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: reduced.matches ? 'auto' : 'smooth', block: 'start' });
        history.replaceState(history.state, '', hash);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initNav();
    initHeroName();
    initScrollChrome();
    initAnchors();
    initScrollSpy();
    initProjectModal();
    initAlerts();
});
