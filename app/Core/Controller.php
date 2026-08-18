<?php

namespace App\Core;

use App\Models\Profile;
use RuntimeException;

/**
 * Base de los controladores: renderiza vistas dentro del layout.
 */
abstract class Controller
{
    protected string $layout = 'main';

    /**
     * Datos disponibles en todas las vistas (perfil, ruta activa, año).
     *
     * @return array<string, mixed>
     */
    protected function sharedData(): array
    {
        return [
            'profile'    => (new Profile())->get(),
            'activePath' => Request::path(),
            'year'       => (int) date('Y'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = [], string $title = '', string $description = ''): void
    {
        $data = array_merge($this->sharedData(), $data);

        $data['pageTitle']       = $title;
        $data['pageDescription'] = $description;

        $content = $this->capture($this->viewPath($view), $data);

        $data['content'] = $content;

        echo $this->capture($this->viewPath('layouts/' . $this->layout), $data);
    }

    /**
     * Renderiza una vista parcial y devuelve su HTML.
     *
     * @param array<string, mixed> $data
     */
    protected function partial(string $view, array $data = []): string
    {
        return $this->capture($this->viewPath('partials/' . $view), $data);
    }

    protected function notFound(string $message = 'Esta página no existe. Puede que el enlace esté mal escrito o que ya no esté disponible.'): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message], 'Página no encontrada');
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    private function viewPath(string $view): string
    {
        return APP_PATH . '/Views/' . $view . '.php';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function capture(string $file, array $data): string
    {
        if (! is_file($file)) {
            throw new RuntimeException('Vista no encontrada: ' . $file);
        }

        // Igual que en `partial()`: la vista se incluye en un ámbito limpio.
        // Con `extract(EXTR_SKIP)`, cualquier variable de este método sería
        // intocable para la vista — pasarle una clave `file` o `data` no
        // haría nada y costaría un rato averiguar por qué.
        return (static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);

            ob_start();
            require $__file;

            return (string) ob_get_clean();
        })($file, $data);
    }
}
