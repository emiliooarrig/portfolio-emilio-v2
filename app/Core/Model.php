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

    // --------------------------------------------------------
    //  Apoyo para la escritura desde el panel
    //
    //  Vive aquí y no en cada modelo porque "esto es una fecha",
    //  "esto es una URL" y "este slug ya está tomado" se responden
    //  igual para un proyecto que para una certificación.
    // --------------------------------------------------------

    /**
     * Cadena vacía → NULL. En la base "sin dato" es NULL, no "": si no se
     * distinguen, un campo opcional vacío se guarda como texto vacío y deja
     * de comportarse como ausente.
     */
    protected function nullify(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** "2024-03-01" y que exista de verdad (no un 31 de febrero). */
    protected function isDate(string $value): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return false;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $value));

        return checkdate($m, $d, $y);
    }

    protected function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $value) === 1;
    }

    protected function isSlug(string $value): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
    }

    /**
     * ¿Ese slug ya es de otra fila? `$ignoreId` es la fila que se está
     * editando: chocar consigo misma no es un choque.
     *
     * El nombre de tabla se interpola porque no puede ir como parámetro;
     * sólo lo llaman los modelos con literales suyos, nunca con entrada
     * del formulario.
     */
    protected function slugTaken(string $table, string $slug, ?int $ignoreId = null): bool
    {
        $row = $this->one(
            "SELECT id FROM `$table` WHERE slug = ? AND id <> ? LIMIT 1",
            [$slug, $ignoreId ?? 0]
        );

        return $row !== null;
    }

    /**
     * Recorta a lo que aguanta la columna. Se valida el largo antes, así que
     * esto sólo es el cinturón: nunca debería tener nada que recortar.
     */
    protected function fit(?string $value, int $max): ?string
    {
        $value = $this->nullify($value);

        return $value === null ? null : mb_substr($value, 0, $max);
    }
}
