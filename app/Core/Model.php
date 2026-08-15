<?php

namespace App\Core;

/**
 * Base de todos los modelos: expone la conexión y helpers de consulta.
 */
abstract class Model
{
    protected function db(): Database
    {
        return Database::instance();
    }

    /**
     * @param  array<int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function all(string $sql, array $params = []): array
    {
        return $this->db()->select($sql, $params);
    }

    /**
     * @param  array<int, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function one(string $sql, array $params = []): ?array
    {
        return $this->db()->selectOne($sql, $params);
    }

    /**
     * Agrupa filas por el valor de una columna (para hidratar relaciones 1:N).
     *
     * @param  array<int, array<string, mixed>> $rows
     * @return array<int|string, array<int, array<string, mixed>>>
     */
    protected function groupBy(array $rows, string $key): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row[$key]][] = $row;
        }

        return $grouped;
    }
}
