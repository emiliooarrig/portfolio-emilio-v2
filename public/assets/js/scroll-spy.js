/**
 * scroll-spy.js — resalta en el nav la sección visible.
 *
 * Sin listeners de scroll: un IntersectionObserver por sección y una franja
 * de detección justo debajo del nav fijo.
 */

export function initScrollSpy() {
    const links = Array.from(document.querySelectorAll('[data-spy-link]'));

    if (links.length === 0 || !('IntersectionObserver' in window)) {
        return;
    }

    /** @type {Map<string, HTMLElement>} */
    const linkBySection = new Map();
    const sections = [];

    links.forEach((link) => {
        const id = (link.getAttribute('href') || '').replace(/^.*#/, '');
        const section = id ? document.getElementById(id) : null;

        if (section) {
            linkBySection.set(id, link);
            sections.push(section);
        }
    });

    if (sections.length === 0) {
        return;
    }

    const visible = new Set();

    const setActive = (id) => {
        linkBySection.forEach((link, key) => {
            const active = key === id;

            link.classList.toggle('is-active', active);

            if (active) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    /** Con varias secciones en pantalla gana la que está más arriba. */
    const sync = () => {
        if (visible.size === 0) {
            return;
        }

        const winner = sections
            .filter((section) => visible.has(section.id))
            .sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top)[0];

        if (winner) {
            setActive(winner.id);
        }
    };

    const navHeight = parseFloat(
        getComputedStyle(document.documentElement).getPropertyValue('--nav-height')
    ) || 4.5;

    const topOffset = Math.round(navHeight * 16) + 8;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                visible.add(entry.target.id);
            } else {
                visible.delete(entry.target.id);
            }
        });

        sync();
    }, { rootMargin: `-${topOffset}px 0px -55% 0px`, threshold: 0 });

    sections.forEach((section) => observer.observe(section));

    // Al final del documento la última sección puede no llegar a la franja.
    const sentinel = document.querySelector('[data-spy-end]');

    if (sentinel) {
        new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setActive(sections[sections.length - 1].id);
                }
            });
        }, { threshold: 0.2 }).observe(sentinel);
    }
}
