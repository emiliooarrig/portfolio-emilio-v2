<?php

namespace App\Core;

/**
 * Archivos que entran por el panel: la foto del perfil y el CV.
 *
 * Quien edita ya no escribe una ruta: elige un archivo y esta clase decide
 * dónde vive y cómo se llama. Cada regla (imagen o documento) trae su
 * carpeta, su peso máximo y los formatos que acepta, y ese mismo dato es el
 * que limita el borrado: `remove()` sólo toca lo que está dentro de la
 * carpeta declarada, así una ruta rara en la base no puede llevarse por
 * delante nada de fuera.
 *
 * El tipo se decide leyendo el archivo, no por su extensión ni por lo que
 * dice el navegador: las dos cosas las escribe quien sube.
 */
final class Upload
{
    /** Formatos admitidos: tipo real del archivo => extensión con la que se guarda. */
    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    private const DOCUMENT_TYPES = [
        'application/pdf' => 'pdf',
    ];

    private string $error = '';

    /**
     * @param string                $directory carpeta pública, relativa a /public
     * @param array<string, string> $types     tipo MIME => extensión
     */
    private function __construct(
        private readonly string $directory,
        private readonly array $types,
        private readonly int $maxBytes,
        private readonly bool $mustBeImage,
    ) {
    }

    public static function image(): self
    {
        return new self('/assets/img', self::IMAGE_TYPES, 4 * 1024 * 1024, true);
    }

    public static function document(): self
    {
        return new self('/assets/docs', self::DOCUMENT_TYPES, 8 * 1024 * 1024, false);
    }

    /**
     * Lo que el formulario necesita saber de la regla: qué ofrecer en el
     * diálogo del sistema y qué advertir antes de elegir.
     *
     * @return array{accept: string, formats: string, limit: string}
     */
    public function rules(): array
    {
        return [
            'accept'  => implode(',', array_keys($this->types)),
            'formats' => $this->formats(),
            'limit'   => $this->limit(),
        ];
    }

    /**
     * ¿Se eligió un archivo en este campo? Un formulario enviado sin tocar
     * el selector no es un error: es "deja lo que ya había".
     */
    public function received(string $field): bool
    {
        $file = $_FILES[$field] ?? null;

        return is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Valida y guarda. Devuelve la ruta pública lista para la base, o null
     * si algo falló — el motivo queda en `error()`.
     *
     * @param string $name base del nombre con el que se guardará
     */
    public function store(string $field, string $name): ?string
    {
        $file = $_FILES[$field] ?? null;

        if (! is_array($file)) {
            return $this->fail('No llegó ningún archivo. Vuelve a elegirlo.');
        }

        $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($code !== UPLOAD_ERR_OK) {
            return $this->fail($this->reason($code));
        }

        $temporary = (string) ($file['tmp_name'] ?? '');

        // El archivo tiene que venir de una subida de verdad: si no, el POST
        // podría estar apuntando a cualquier ruta del servidor.
        if ($temporary === '' || ! is_uploaded_file($temporary)) {
            return $this->fail('No llegó ningún archivo. Vuelve a elegirlo.');
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0) {
            return $this->fail('El archivo llegó vacío.');
        }

        if ($size > $this->maxBytes) {
            return $this->fail('El archivo pesa más de ' . $this->limit() . '.');
        }

        $extension = $this->types[$this->mimeOf($temporary)] ?? null;

        if ($extension === null) {
            return $this->fail('Ese formato no se admite aquí. Sube ' . $this->formats() . '.');
        }

        // Un tipo de imagen no basta: getimagesize confirma que dentro hay
        // una imagen y no otra cosa con la cabecera cambiada.
        if ($this->mustBeImage && @getimagesize($temporary) === false) {
            return $this->fail('Ese archivo dice ser una imagen, pero no se puede leer como tal.');
        }

        $folder = PUBLIC_PATH . $this->directory;

        if (! is_dir($folder) && ! @mkdir($folder, 0775, true) && ! is_dir($folder)) {
            return $this->fail('No se pudo crear la carpeta ' . $this->directory . ' en el servidor.');
        }

        if (! is_writable($folder)) {
            return $this->fail('La carpeta ' . $this->directory . ' no tiene permiso de escritura.');
        }

        $filename = $this->filename($name, $extension);

        if (! @move_uploaded_file($temporary, $folder . '/' . $filename)) {
            return $this->fail('No se pudo guardar el archivo en el servidor.');
        }

        return $this->directory . '/' . $filename;
    }

    /**
     * Borra un archivo al que ya no apunta nadie. Devuelve si se borró.
     */
    public function remove(?string $publicPath): bool
    {
        $path = trim((string) $publicPath);

        if ($path === '' || ! str_starts_with($path, $this->directory . '/')) {
            return false;
        }

        $folder = realpath(PUBLIC_PATH . $this->directory);
        $target = realpath(PUBLIC_PATH . $path);

        // realpath resuelve los `..`: si después de resolverlos el archivo no
        // sigue dentro de la carpeta, no es asunto nuestro.
        if ($folder === false || $target === false) {
            return false;
        }

        if (! str_starts_with($target, $folder . DIRECTORY_SEPARATOR) || ! is_file($target)) {
            return false;
        }

        return @unlink($target);
    }

    /** Por qué falló el último `store()`. */
    public function error(): string
    {
        return $this->error;
    }

    /** "JPG, PNG, WEBP o AVIF" */
    public function formats(): string
    {
        $extensions = array_map('strtoupper', array_values($this->types));
        $last       = array_pop($extensions);

        return $extensions === [] ? $last : implode(', ', $extensions) . ' o ' . $last;
    }

    /** "4 MB" */
    public function limit(): string
    {
        return round($this->maxBytes / 1024 / 1024) . ' MB';
    }

    /**
     * Nombre propio: legible por el slug y único por el sufijo, que además
     * evita que el navegador siga mostrando la foto anterior de su caché.
     */
    private function filename(string $name, string $extension): string
    {
        $slug = slugify($name);

        return ($slug === '' ? 'archivo' : $slug) . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    private function mimeOf(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return '';
        }

        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return is_string($mime) ? $mime : '';
    }

    /** Traduce el código de PHP a algo que se pueda leer y corregir. */
    private function reason(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo pesa más de lo que admite el servidor.',
            UPLOAD_ERR_PARTIAL                        => 'La subida se cortó a medias. Inténtalo otra vez.',
            UPLOAD_ERR_NO_FILE                        => 'No elegiste ningún archivo.',
            default                                   => 'El servidor no pudo recibir el archivo.',
        };
    }

    private function fail(string $message): null
    {
        $this->error = $message;

        return null;
    }
}
