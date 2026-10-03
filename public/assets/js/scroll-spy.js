/**
 * scroll-spy.js — resalta en el nav la sección visible. Sin listeners de
 * scroll: un IntersectionObserver con una franja justo bajo el nav.
 */

export function initScrollSpy() {
    const links = new Map();
    const sections = [];

    document.querySelectorAll('[data-spy-link]').forEach((link) => {
        const id = (link.getAttribute('href') || '').replace(/^.*#/, '');
        const section = id && document.getElementById(id);

        if (section) {
            links.set(id, link);
            sections.push(section);
        }
    });

    if (sections.length === 0 || !('IntersectionObserver' in window)) {
        return;
    }

    const visible = new Set();

    const setActive = (id) => links.forEach((link, key) => {
        link.classList.toggle('is-active', key === id);
        key === id ? link.setAttribute('aria-current', 'true') : link.removeAttribute('aria-current');
    });

    const nav = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--nav-height')) || 4;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => visible[entry.isIntersecting ? 'add' : 'delete'](entry.target.id));

        // Con varias secciones en pantalla gana la de más arriba.
        const winner = sections
            .filter((section) => visible.has(section.id))
            .sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top)[0];

        if (winner) {
            setActive(winner.id);
        }
    }, { rootMargin: `-${Math.round(nav * 16) + 8}px 0px -55% 0px` });

    sections.forEach((section) => observer.observe(section));

    // Al final del documento la última sección puede no llegar a la franja.
    const end = document.querySelector('[data-spy-end]');

    if (end) {
        new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                setActive(sections[sections.length - 1].id);
            }
        }, { threshold: 0.2 }).observe(end);
    }
}
