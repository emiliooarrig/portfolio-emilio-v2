/**
 * marquee.js — la cinta se acelera un poco mientras se hace scroll.
 *
 * El bucle infinito es CSS (_marquee.scss). Aquí sólo se ajusta su
 * velocidad: la del scroll entra en `--marquee-speed`, suavizada en el
 * bucle compartido, y se aplica como playbackRate de la animación.
 */

import { animate } from './loop.js';

export function initMarquee() {
    const marquee = document.querySelector('[data-marquee]');
    const track = marquee?.querySelector('.marquee__track');

    if (!track || matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let lastY = window.scrollY;
    let target = 1;
    let speed = 1;
    let running = false;

    const step = () => {
        target += (1 - target) * 0.08;   // el empuje del scroll se apaga solo
        speed += (target - speed) * 0.12;

        const animation = track.getAnimations()[0];

        if (animation) {
            animation.playbackRate = speed;
        }

        marquee.style.setProperty('--marquee-speed', speed.toFixed(3));
        running = Math.abs(speed - 1) > 0.005 || Math.abs(target - 1) > 0.005;

        return running;
    };

    window.addEventListener('scroll', () => {
        const y = window.scrollY;

        target = Math.min(4, target + Math.abs(y - lastY) / 60);
        lastY = y;

        if (!running) {
            running = true;
            animate(step);
        }
    }, { passive: true });
}
