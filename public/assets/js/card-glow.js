/**
 * card-glow.js — origen de la iluminación progresiva de los contenedores.
 *
 * Sirve a toda celda con .bento-card--hoverable, que es quien define el
 * efecto en CSS: una sola fuente de verdad, sin lista de selectores paralela.
 *
 * El brillo y su rampa viven en CSS; aquí sólo se marca *dónde* nace la luz:
 * el punto por el que el puntero entró a la celda. Se lee una vez por entrada
 * (no en cada movimiento) para que la luz crezca desde ahí en vez de perseguir
 * al cursor, y para no medir layout en cada frame.
 *
 * Va por delegación en el documento: así el detalle del proyecto, que llega
 * por fetch después de cargar la página, queda cubierto sin volver a atar nada.
 */

const SELECTOR = '.bento-card--hoverable';

function setOrigin(cell, x, y) {
    const rect = cell.getBoundingClientRect();

    if (rect.width === 0 || rect.height === 0) {
        return;
    }

    cell.style.setProperty('--glow-x', `${(((x - rect.left) / rect.width) * 100).toFixed(2)}%`);
    cell.style.setProperty('--glow-y', `${(((y - rect.top) / rect.height) * 100).toFixed(2)}%`);
}

export function initCardGlow() {
    // Sin puntero fino no hay punto de entrada que valga: se queda el
    // origen por defecto del CSS.
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    // La celda encendida no vuelve a recalcular su origen: pointerover se
    // dispara también al pasar de un hijo a otro dentro de la misma celda.
    let active = null;

    document.addEventListener('pointerover', (event) => {
        const cell = event.target.closest(SELECTOR);

        if (!cell) {
            active = null;

            return;
        }

        if (cell === active) {
            return;
        }

        active = cell;
        setOrigin(cell, event.clientX, event.clientY);
    }, { passive: true });

    // El foco por teclado no trae coordenadas: la luz nace centrada.
    document.addEventListener('focusin', (event) => {
        const cell = event.target.closest(SELECTOR);

        if (!cell || cell === active) {
            return;
        }

        cell.style.setProperty('--glow-x', '50%');
        cell.style.setProperty('--glow-y', '50%');
    });
}
