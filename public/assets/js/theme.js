/**
 * theme.js — botón claro/oscuro. El tema inicial lo pone el <head> (sin
 * destello); aquí se cambia, se recuerda y, con View Transitions, se
 * revela en un círculo que crece desde el botón.
 */

const COLORS = { light: '#F5F6F8', dark: '#0E1014' };

export function initTheme() {
    const button = document.querySelector('[data-theme-toggle]');

    if (!button) {
        return;
    }

    const root = document.documentElement;
    const meta = document.querySelector('meta[name="theme-color"]');

    const sync = () => {
        const dark = root.dataset.theme === 'dark';

        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('aria-label', dark ? 'Activar modo claro' : 'Activar modo oscuro');
        meta?.setAttribute('content', COLORS[dark ? 'dark' : 'light']);
    };

    sync();

    button.addEventListener('click', () => {
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';

        const apply = () => {
            root.dataset.theme = next;
            try { localStorage.setItem('theme', next); } catch (e) { /* sin almacenamiento: sólo esta visita */ }
            sync();
        };

        // Sin la API o con movimiento reducido el cambio es instantáneo: la
        // clase también apaga el fundido de tokens del <body>.
        if (!document.startViewTransition || matchMedia('(prefers-reduced-motion: reduce)').matches) {
            root.classList.add('is-theme-switching');
            apply();
            requestAnimationFrame(() => requestAnimationFrame(() => root.classList.remove('is-theme-switching')));

            return;
        }

        const rect = button.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;

        root.style.setProperty('--vt-x', `${x}px`);
        root.style.setProperty('--vt-y', `${y}px`);
        root.style.setProperty('--vt-r', `${Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y))}px`);
        root.classList.add('is-theme-switching');

        document.startViewTransition(apply).finished.finally(() => root.classList.remove('is-theme-switching'));
    });
}
