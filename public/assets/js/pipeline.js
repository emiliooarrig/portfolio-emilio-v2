/**
 * pipeline.js — control del signature element (panel de vidrio).
 *
 * La animación de los paquetes de datos vive en CSS; aquí sólo se pausa
 * cuando el panel no está visible, para no gastar batería ni GPU.
 */

export function initPipeline() {
    const panels = document.querySelectorAll('[data-pipeline]');

    if (panels.length === 0) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (reducedMotion.matches) {
        panels.forEach((panel) => panel.classList.add('is-paused'));

        return;
    }

    if (!('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            entry.target.classList.toggle('is-paused', !entry.isIntersecting);
        });
    }, { threshold: 0.05 });

    panels.forEach((panel) => observer.observe(panel));

    // Pausar también con la pestaña en segundo plano.
    document.addEventListener('visibilitychange', () => {
        panels.forEach((panel) => {
            panel.classList.toggle('is-paused', document.hidden);
        });
    });
}
