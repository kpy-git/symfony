<?php

namespace App\Shared\Infrastructure\Database;

use PDO;
use PDOException;
use PDOStatement;

class Database implements DatabaseInterface
{
    protected ?PDO $link = null;

    private string $lastSql = '';

    private string $sqlError = '';

    private array $options;

    public function __construct(
        private readonly string $dsn,
        private readonly string $username,
        private readonly string $password,
        array                   $options = []
    )
    {
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            ...$options
        ];
    }

    /**
     * @throws PDOException
     */
    protected function connect(): void
    {
        try {
            $this->link = new PDO($this->dsn, $this->username, $this->password, $this->options);

        } catch (PDOException $exception) {
            $this->sqlError = $exception->getMessage();
            throw $exception;
        }
    }

    /**
     * @throws PDOException
     */
    public function execute(string $sql, int $mode = PDO::FETCH_ASSOC): bool|array
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        if ($this->isDML($sql)) {
            // podría devolver 0 filas afectadas, por eso se compara con false
            return $this->link->exec($sql) !== false;
        }

        return $this->link->query($sql)->fetchAll($mode);
    }

    /**
     * @throws PDOException
     */
    private function initializeConnection(): void
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $this->lastSql = '';
        $this->sqlError = '';
    }

    public function isConnected(): bool
    {
        return $this->link !== null;
    }

    private function isDML(string $sql): bool
    {
        $sqlSanitized = mb_strtoupper(trim($sql));

        return str_starts_with($sqlSanitized, 'UPDATE')
            || str_starts_with($sqlSanitized, 'INSERT')
            || str_starts_with($sqlSanitized, 'TRUNCATE')
            || str_starts_with($sqlSanitized, 'DELETE');
    }

    /**
     * @throws PDOException
     */
    public function getRow(string $sql): array
    {
        if ($this->isDML($sql)) {
            return [];
        }

        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->link->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function withError(): bool
    {
        return $this->sqlError !== '' || $this->link->errorCode() !== '00000';
    }

    /**
     * @throws PDOException
     */
    public function insert(string $table, array $data): bool
    {
        return $this->insertMany($table, [$data]);
    }

    /**
     * @throws PDOException
     */
    public function insertMany(string $table, array $data, int $maxValueInsert = self::MAX_ROWS_BATCH): int
    {
        $this->initializeConnection();
        //Will contain SQL snippets.
        $rowsSQL = [];

        //Will contain the values that we need to bind.
        $toBind = [];

        //Get a list of column names to use in the SQL statement.
        $columnNames = array_keys($data[0]);

        //Loop through our $data array.
        foreach ($data as $arrayIndex => $row) {
            $params = array();

            foreach ($row as $columnName => $columnValue) {
                $param = ":" . $columnName . $arrayIndex;
                $params[] = $param;
                $toBind[$param] = $columnValue;
            }
            $rowsSQL[] = "(" . implode(", ", $params) . ")";
        }

        $chunks = array_chunk($rowsSQL, $maxValueInsert);

        foreach ($chunks as $part => $values) {
            $sql = "INSERT INTO {$table} (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $values);
            $this->lastSql = $sql;
            $stmt = $this->prepare($sql);

            $binds = array_slice($toBind, $part*$maxValueInsert*count($columnNames), $maxValueInsert*count($columnNames), true);
            foreach ($binds as $param => $val) {
                $stmt->bindValue($param, $val);
            }

            $stmt->execute();
        }

        return count($rowsSQL);
    }

    /**
     * Obtiene el valor de la primera fila y la primera columna de los resultados de la consulta
     *
     * @throws PDOException
     */
    public function getValue(string $sql): mixed
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->link->query($sql, PDO::FETCH_NUM)->fetchColumn();
    }

    public function getSqlError(): string
    {
        return $this->sqlError;
    }

    public function getLastSql(): string
    {
        return preg_replace('/\s\s+/', ' ', $this->lastSql);
    }

    /**
     * @throws PDOException
     */
    public function beginTransaction(): void
    {
        $this->initializeConnection();
        $this->link->beginTransaction();
    }

    /**
     * @throws PDOException
     */
    public function commit(): void
    {
        $this->link->commit();
    }

    /**
     * @throws PDOException
     */
    public function rollback(): void
    {
        $this->link->rollback();
    }

    /**
     * @throws PDOException
     */
    public function prepare(string $sql, array $options = []): PDOStatement
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->link->prepare($sql, $options);
    }

    /**
     * @throws PDOException
     */
    public function prepareForSelect(string $sql): PDOStatement
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }
}
