/**
 * loop.js — el único bucle requestAnimationFrame de la landing.
 *
 * Lo comparten el cursor, la vista previa de proyectos y la velocidad de
 * la cinta. Cada tarea devuelve `true` mientras le quede algo que mover;
 * cuando ninguna lo necesita, el bucle se detiene. Con la pestaña oculta
 * también se detiene, y se reanuda al volver si hay tareas.
 */

const tasks = new Set();
let frame = 0;

function tick(time) {
    frame = 0;

    tasks.forEach((task) => {
        if (!task(time)) {
            tasks.delete(task);
        }
    });

    if (tasks.size > 0 && !document.hidden) {
        frame = requestAnimationFrame(tick);
    }
}

/** Pide frames para `task` hasta que devuelva falso. */
export function animate(task) {
    tasks.add(task);

    if (!frame && !document.hidden) {
        frame = requestAnimationFrame(tick);
    }
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        cancelAnimationFrame(frame);
        frame = 0;
    } else if (tasks.size > 0 && !frame) {
        frame = requestAnimationFrame(tick);
    }
});
