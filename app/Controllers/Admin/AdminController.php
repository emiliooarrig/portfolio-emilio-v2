<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Upload;

/**
 * Base de las pantallas del panel.
 *
 * Reúne lo que toda sección repite: el layout con barra lateral, quién está
 * dentro, el token de formulario, el aviso de la acción anterior y la
 * lectura del POST. Lo que **no** hace es proteger sola: cada acción llama a
 * `Auth::requireLogin()` en su primera línea, aunque se repita ocho veces.
 * Es la línea que sostiene todo el panel; escrita a la vista no se olvida.
 *
 * Toda escritura sigue el mismo camino: POST con token → validar → guardar
 * → redirigir con un aviso. Nunca se responde a un POST con HTML: recargar
 * después de guardar volvería a guardar.
 */
abstract class AdminController extends Controller
{
    protected string $layout = 'admin';

    /**
     * Datos que el layout del panel necesita en todas las pantallas.
     *
     * @return array<string, mixed>
     */
    protected function sharedData(): array
    {
        return array_merge(parent::sharedData(), [
            'admin' => Auth::user(),
            'token' => Csrf::token(),
            'flash' => $this->takeFlash(),
        ]);
    }

    // --------------------------------------------------------
    //  Escritura
    // --------------------------------------------------------

    /**
     * Puerta de toda acción que escribe: sesión y token.
     *
     * Sin token no se toca la base y se vuelve por donde se vino — que es
     * lo que pasa cuando el POST lo publica otra página, o cuando el
     * formulario llevaba horas abierto y la sesión ya se renovó.
     */
    protected function requireToken(string $back): void
    {
        Auth::requireLogin();

        if (Csrf::check(Request::input('_token'))) {
            return;
        }

        $this->flash('error', 'La sesión del formulario caducó. Vuelve a intentarlo.');
        $this->redirect($back);
    }

    /**
     * La fila que se pedía no está: se avisa y se vuelve al listado, en vez
     * de un 404 que dejaría al panel sin salida.
     */
    protected function missing(string $back, string $what): void
    {
        $this->flash('error', $what . ' no existe: puede que ya se haya borrado.');
        $this->redirect($back);
    }

    /**
     * Lo que pinta el formulario: los valores de la base, pisados por lo que
     * se tecleó en el intento anterior si ese intento falló.
     *
     * @param  array<string, mixed> $defaults
     * @param  array<string, mixed> $old
     * @return array<string, mixed>
     */
    protected function values(array $defaults, array $old): array
    {
        return array_merge($defaults, array_intersect_key($old, $defaults));
    }

    /** Aviso para la siguiente pantalla (se muestra una vez y se borra). */
    protected function flash(string $type, string $text): void
    {
        $_SESSION['admin_flash'] = ['type' => $type, 'text' => $text];
    }

    /**
     * Vuelve al formulario con los errores y lo ya tecleado, para no obligar
     * a escribirlo todo otra vez.
     *
     * @param array<string, string> $errors
     * @param array<string, mixed>  $input
     */
    protected function backWithErrors(string $path, array $errors, array $input): void
    {
        $_SESSION['admin_form_errors'] = $errors;
        $_SESSION['admin_form_old']    = $input;

        $this->flash('error', 'Revisa los campos marcados.');
        $this->redirect($path);
    }

    /**
     * Errores y valores del intento anterior. Se leen una vez: si siguieran
     * en sesión, reaparecerían en el siguiente formulario que se abriera.
     *
     * @return array{0: array<string, string>, 1: array<string, mixed>}
     */
    protected function takeFormState(): array
    {
        $errors = $_SESSION['admin_form_errors'] ?? [];
        $old    = $_SESSION['admin_form_old'] ?? [];

        unset($_SESSION['admin_form_errors'], $_SESSION['admin_form_old']);

        return [
            is_array($errors) ? $errors : [],
            is_array($old) ? $old : [],
        ];
    }

    // --------------------------------------------------------
    //  Archivos del formulario
    // --------------------------------------------------------
    //
    //  Quien edita no escribe rutas: elige un archivo y el panel decide
    //  dónde vive. Cada columna de la base se declara con su regla de
    //  subida y estos tres pasos hacen el resto, iguales en todas las
    //  secciones: conservar lo que había, guardar lo que llegue y borrar
    //  lo que dejó de usarse.

    /**
     * Un POST que se pasa de `post_max_size` llega vacío: sin token y sin
     * campos. Sin este aviso el panel diría que caducó la sesión, cuando el
     * problema es el peso del archivo. Va antes del token porque el token
     * tampoco llegó.
     */
    protected function guardUploadSize(string $back): void
    {
        if ($_POST !== [] || (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) <= 0) {
            return;
        }

        $this->flash('error', 'El envío pesa más de lo que admite el servidor. Sube un archivo más ligero.');
        $this->redirect($back);
    }

    /**
     * Punto de partida: cada columna de archivo arranca con la ruta que ya
     * tenía la fila. Mientras nadie suba nada ni marque «quitar», el
     * formulario no cambia el archivo. En un alta no hay fila y quedan
     * vacías.
     *
     * @param  array<string, mixed>                                              $input
     * @param  array<string, array{field: string, upload: Upload, name: string}> $files   columna de la base => regla
     * @param  array<string, mixed>|null                                         $current fila que se está editando
     * @return array<string, mixed>
     */
    protected function keepFiles(array $input, array $files, ?array $current = null): array
    {
        foreach (array_keys($files) as $column) {
            $input[$column] = (string) ($current[$column] ?? '');
        }

        return $input;
    }

    /**
     * Guarda los archivos que llegaron y decide qué ruta acaba en cada
     * columna. Se llama cuando el resto del formulario ya está bien: así no
     * queda nada suelto en /public sin fila que lo apunte.
     *
     * Lo que deja de usarse no se borra aquí, se devuelve: el disco se toca
     * **después** de guardar, porque si el guardado fallara el archivo viejo
     * tiene que seguir donde la fila dice que está.
     *
     * @param  array<string, mixed>                                              $input
     * @param  array<string, array{field: string, upload: Upload, name: string}> $files
     * @return array{input: array<string, mixed>, errors: array<string, string>, discarded: array<string, string>}
     */
    protected function storeFiles(array $input, array $files): array
    {
        $errors   = [];
        $uploaded = [];

        foreach ($files as $column => $file) {
            if (! $file['upload']->received($file['field'])) {
                continue;
            }

            $path = $file['upload']->store($file['field'], $file['name']);

            if ($path === null) {
                $errors[$file['field']] = $file['upload']->error();

                continue;
            }

            $uploaded[$column] = $path;
        }

        if ($errors !== []) {
            // Se aborta el guardado: lo que sí se subió no lo va a apuntar
            // ninguna fila, así que se retira en vez de quedarse suelto.
            foreach ($uploaded as $column => $path) {
                $files[$column]['upload']->remove($path);
            }

            return ['input' => $input, 'errors' => $errors, 'discarded' => []];
        }

        $discarded = [];

        foreach ($files as $column => $file) {
            $remove = $this->postFlag('remove_' . $file['field']) === 1;

            if (! isset($uploaded[$column]) && ! $remove) {
                continue;
            }

            $discarded[$column] = (string) $input[$column];
            $input[$column]     = $uploaded[$column] ?? '';
        }

        return ['input' => $input, 'errors' => [], 'discarded' => $discarded];
    }

    /**
     * Borra del disco los archivos que ya no apunta nadie. Cada regla sólo
     * puede tocar su propia carpeta, así que una ruta rara en la base no se
     * lleva por delante nada de fuera.
     *
     * @param array<string, array{field: string, upload: Upload, name: string}> $files
     * @param array<string, string>                                             $discarded
     */
    protected function dropFiles(array $files, array $discarded): void
    {
        foreach ($discarded as $column => $previous) {
            $files[$column]['upload']->remove($previous);
        }
    }

    // --------------------------------------------------------
    //  Lectura del POST
    // --------------------------------------------------------

    protected function postText(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? trim($this->utf8($value)) : '';
    }

    /**
     * Deja el texto en UTF-8 válido antes de que llegue a la base.
     *
     * Un navegador sobre una página UTF-8 manda siempre UTF-8, así que esto
     * sólo salta con un POST hecho a mano con otra codificación. Y ahí
     * importa: la conexión es utf8mb4 y MySQL rechaza el byte suelto con un
     * error que tumbaría la petición entera. Mejor guardar el carácter de
     * reemplazo, que se ve y se corrige, que devolver un 500.
     */
    private function utf8(string $value): string
    {
        return mb_check_encoding($value, 'UTF-8') ? $value : mb_scrub($value, 'UTF-8');
    }

    protected function postInt(string $key): int
    {
        return (int) ($_POST[$key] ?? 0);
    }

    /** Una casilla marcada llega en el POST; una sin marcar, no llega. */
    protected function postFlag(string $key): int
    {
        return isset($_POST[$key]) ? 1 : 0;
    }

    /**
     * Textarea de una cosa por línea → lista limpia, sin vacías.
     *
     * @return array<int, string>
     */
    protected function postLines(string $key): array
    {
        $raw = $_POST[$key] ?? '';

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];

        $lines = array_map(fn (string $line): string => trim($this->utf8($line)), $lines);

        return array_values(array_filter($lines, static fn (string $l): bool => $l !== ''));
    }

    /**
     * Grupos de campos indexados (`metrics[0][label]`) → filas.
     *
     * Se descarta la fila cuya primera columna quedó vacía: así vaciar el
     * campo principal es la manera de borrar una fila, sin botón aparte.
     *
     * @param  array<int, string> $columns  la primera manda: si está vacía, no hay fila
     * @return array<int, array<string, string>>
     */
    protected function postRows(string $key, array $columns): array
    {
        $raw = $_POST[$key] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $rows = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $row = [];

            foreach ($columns as $column) {
                $value        = $entry[$column] ?? '';
                $row[$column] = is_string($value) ? trim($this->utf8($value)) : '';
            }

            if ($row[$columns[0]] === '') {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Casillas o select múltiple de ids.
     *
     * @return array<int, int>
     */
    protected function postIds(string $key): array
    {
        $raw = $_POST[$key] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $ids = array_map('intval', $raw);

        return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
    }

    /** Aviso una sola vez: se lee y se borra. @return array<string, string>|null */
    private function takeFlash(): ?array
    {
        $flash = $_SESSION['admin_flash'] ?? null;

        unset($_SESSION['admin_flash']);

        return is_array($flash) ? $flash : null;
    }
}
