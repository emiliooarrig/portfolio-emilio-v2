<?php

namespace App\Models;

use App\Core\Model;

/**
 * Métricas pequeñas del bento de Inicio.
 */
class HomeMetric extends Model
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function published(int $limit = 4): array
    {
        return $this->all(
            'SELECT label, value, unit, caption
               FROM home_metrics
              WHERE is_published = 1
           ORDER BY sort_order ASC, id ASC
              LIMIT ?',
            [$limit]
        );
    }
}
