<?php

namespace App\Core;

use mysqli;
use mysqli_sql_exception;
use RuntimeException;

/**
 * Envoltura ligera sobre mysqli con sentencias preparadas.
 * Una sola conexión por petición (singleton).
 */
class Database
{
    private static ?Database $instance = null;

    private mysqli $connection;

    private function __construct(array $config)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(
                $config['host'],
                $config['user'],
                $config['pass'],
                $config['name'],
                (int) $config['port']
            );
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException(
                'No fue posible conectar con la base de datos `' . $config['name'] . '`.',
                0,
                $e
            );
        }

        $this->connection->set_charset($config['charset'] ?? 'utf8mb4');
    }

    public static function instance(?array $config = null): self
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new RuntimeException('La base de datos no ha sido inicializada.');
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public function connection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Devuelve todas las filas de una consulta.
     *
     * @param  array<int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $params = []): array
    {
        $statement = $this->prepare($sql, $params);
        $statement->execute();

        $result = $statement->get_result();
        $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        $statement->close();

        return $rows;
    }

    /**
     * Devuelve la primera fila o null.
     *
     * @param  array<int, mixed> $params
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $rows = $this->select($sql, $params);

        return $rows[0] ?? null;
    }

    /**
     * Ejecuta INSERT / UPDATE / DELETE. Devuelve el id insertado o las filas afectadas.
     *
     * @param array<int, mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        $statement = $this->prepare($sql, $params);
        $statement->execute();

        $insertId = $this->connection->insert_id;
        $affected = $statement->affected_rows;

        $statement->close();

        return $insertId > 0 ? $insertId : $affected;
    }

    /**
     * @param array<int, mixed> $params
     */
    private function prepare(string $sql, array $params): \mysqli_stmt
    {
        $statement = $this->connection->prepare($sql);

        if ($params !== []) {
            $statement->bind_param($this->typesFor($params), ...$params);
        }

        return $statement;
    }

    /**
     * @param array<int, mixed> $params
     */
    private function typesFor(array $params): string
    {
        $types = '';

        foreach ($params as $param) {
            $types .= match (true) {
                is_int($param)   => 'i',
                is_float($param) => 'd',
                is_null($param)  => 's',
                default          => 's',
            };
        }

        return $types;
    }
}
