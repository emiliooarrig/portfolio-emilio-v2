/**
 * main.js — arranque del front-end de la landing.
 * Módulos ES nativos, sin dependencias.
 */

import { initScrollReveal } from './scroll-reveal.js';
import { initScrollSpy } from './scroll-spy.js';
import { initCountUp } from './count-up.js';
import { initProjectModal } from './project-modal.js';
import { initPipeline } from './pipeline.js';
import { initCardGlow } from './card-glow.js';
import { initServicesCarousel } from './services-carousel.js';
import { initCursor } from './cursor.js';
import { initHeroBrush } from './hero-brush.js';
import { initAlerts } from './alerts.js';

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
 * Estado del nav, barra de progreso y botón "volver arriba".
 *
 * Un único listener de scroll pasivo, agrupado en requestAnimationFrame:
 * nunca se lee ni se escribe layout dentro del propio evento.
 */
function initScrollChrome() {
    const nav = document.querySelector('[data-nav]');
    const progress = document.querySelector('[data-scroll-progress]');
    const toTop = document.querySelector('[data-to-top]');

    if (!nav && !progress && !toTop) {
        return;
    }

    let ticking = false;

    const update = () => {
        ticking = false;

        const scrolled = window.scrollY;
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const ratio = max > 0 ? Math.min(scrolled / max, 1) : 0;

        nav?.classList.toggle('is-scrolled', scrolled > 24);
        progress?.style.setProperty('--scroll-progress', ratio.toFixed(4));
        toTop?.classList.toggle('is-visible', scrolled > window.innerHeight * 0.8);
    };

    const onScroll = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(update);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
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
    initNav();
    initScrollChrome();
    initAnchors();
    initScrollReveal();
    initScrollSpy();
    initCountUp();
    initProjectModal();
    initPipeline();
    initCardGlow();
    initServicesCarousel();
    initCursor();
    initHeroBrush();
    initAlerts();
});
