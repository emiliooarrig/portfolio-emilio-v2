/**
 * main.js — arranque del front-end de la landing (módulos ES, sin
 * dependencias). Aquí viven las piezas pequeñas: menú móvil, nombre del
 * hero, estado del nav al hacer scroll y anclas.
 */

import { initScrollSpy } from './scroll-spy.js';
import { initProjectModal } from './project-modal.js';
import { initAlerts } from './alerts.js';
import { initTheme } from './theme.js';
import { initScrollReveal } from './scroll-reveal.js';
import { initMarquee } from './marquee.js';
import { initCursor } from './cursor.js';

const REDUCED = matchMedia('(prefers-reduced-motion: reduce)');

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

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

    // Al saltar a una sección, con Escape o al volver a escritorio, se cierra.
    menu.addEventListener('click', (event) => event.target.closest('a') && setOpen(false));
    document.addEventListener('keydown', (event) => event.key === 'Escape' && setOpen(false));
    matchMedia('(min-width: 60rem)').addEventListener('change', (event) => event.matches && setOpen(false));
}

/**
 * El nombre se asienta de un ancho condensado a su ancho final, una vez,
 * al cargar (`_hero.scss`). Se espera a Hubot Sans para no animar la
 * fuente de respaldo; si no llega en 1,2 s, se muestra el estado final.
 */
function initHeroName() {
    const name = document.querySelector('[data-hero-name]');

    if (!name) {
        return;
    }

    if (REDUCED.matches || !document.fonts) {
        name.classList.add('is-set');

        return;
    }

    const timeout = new Promise((resolve) => setTimeout(resolve, 1200, 'timeout'));

    Promise.race([document.fonts.load('800 1em "Hubot Sans"'), timeout])
        .then((result) => name.classList.add(result === 'timeout' ? 'is-set' : 'is-animating'))
        .catch(() => name.classList.add('is-set'));
}

/** Regla bajo el nav e indicador de scroll (`html.is-scrolled`), en rAF. */
function initScrollChrome() {
    const nav = document.querySelector('[data-nav]');
    let ticking = false;

    const update = () => {
        const scrolled = scrollY > 8;

        ticking = false;
        nav?.classList.toggle('is-scrolled', scrolled);
        document.documentElement.classList.toggle('is-scrolled', scrolled);
    };

    addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });

    update();
}

/** Anclas internas: scroll suave sin ensuciar el historial. */
function initAnchors() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="#"], a[data-anchor]');
        const hash = link && !link.hasAttribute('data-modal-scroll') ? (link.getAttribute('href') || '').replace(/^.*(?=#)/, '') : '';
        const target = hash.length > 1 ? document.querySelector(hash) : null;

        if (target) {
            event.preventDefault();
            target.scrollIntoView({ behavior: REDUCED.matches ? 'auto' : 'smooth', block: 'start' });
            history.replaceState(history.state, '', hash);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initNav();
    initHeroName();
    initScrollChrome();
    initScrollReveal();
    initMarquee();
    initCursor();
    initAnchors();
    initScrollSpy();
    initProjectModal();
    initAlerts();
});
