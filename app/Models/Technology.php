<?php

namespace App\Models;

use App\Core\Model;

/**
 * Stack tecnológico: chips del hero y tags de proyecto.
 */
class Technology extends Model
{
    /**
     * Tecnologías marcadas para el bento de stack en Inicio.
     *
     * @return array<int, array<string, mixed>>
     */
    public function featured(int $limit = 10): array
    {
        return $this->all(
            'SELECT id, name, slug, category
               FROM technologies
              WHERE is_featured = 1
           ORDER BY sort_order ASC, name ASC
              LIMIT ?',
            [$limit]
        );
    }

    /**
     * Todo el stack agrupado por categoría (para Sobre mí).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function groupedByCategory(): array
    {
        $rows = $this->all(
            'SELECT id, name, slug, category
               FROM technologies
           ORDER BY category ASC, sort_order ASC, name ASC'
        );

        return $this->groupBy($rows, 'category');
    }

    /**
     * Etiqueta legible de cada categoría.
     */
    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'lenguaje'     => 'Lenguajes',
            'base_datos'   => 'Bases de datos',
            'orquestacion' => 'Orquestación y procesamiento',
            'cloud'        => 'Nube',
            'bi'           => 'Visualización',
            default        => 'Herramientas',
        };
    }
}
