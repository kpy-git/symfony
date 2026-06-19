<?php

namespace App\Database;

use App\Exception\KpySqlException;
use PDO;
use PDOException;
use PDOStatement;

class Database
{
    public const int MAX_ROWS_BATCH = 500;

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
     * @throws KpySqlException
     */
    protected function initializeConnection(): void
    {
        $this->connect();
        $this->lastSql = '';
        $this->sqlError = '';
    }

    /**
     * @throws KpySqlException
     */
    protected function connect(): void
    {
        if ($this->link !== null) {
            return;
        }

        try {
            $this->link = new PDO($this->dsn, $this->username, $this->password);

            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->sqlError = '';

        } catch (PDOException $e) {
            $this->sqlError = $e->getMessage();
            throw new KpySqlException($e->getMessage(), __METHOD__, '', '');
        }
    }

    /**
     * @throws KpySqlException
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

    private function isDML(string $sql): bool
    {
        $sqlSanitized = mb_strtoupper(trim($sql));

        return str_starts_with($sqlSanitized, 'UPDATE')
            || str_starts_with($sqlSanitized, 'INSERT')
            || str_starts_with($sqlSanitized, 'DELETE');
    }

    /**
     * @throws KpySqlException
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
     * @throws KpySqlException
     */
    public function insert(string $table, array $data): bool
    {
        return $this->insertMany($table, [$data]);
    }

    /**
     * @throws KpySqlException
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
            $params = [];

            foreach ($row as $columnName => $columnValue) {
                $param = ":" . $columnName . $arrayIndex;
                $params[] = $param;
                $toBind[$param] = $columnValue;
            }
            $rowsSQL[] = "(" . implode(", ", $params) . ")";
        }

        $row_count = count($rowsSQL);

        $sql = "INSERT INTO $table (" . implode(',', $columnNames) . ") VALUES ";
        $firstRow = true;

        foreach ($rowsSQL as $index => $rowSQL) {
            if ($firstRow) {
                $firstRow = false;
            } else {
                $sql .= ',';
            }

            $sql .= $rowSQL;

            if ($index === $row_count - 1 || $index % self::MAX_ROWS_BATCH === 0) {
                $this->lastSql = $sql;
                $stmt = $this->prepare($sql);

                foreach ($toBind as $param => $value) {
                    $stmt->bindValue($param, $value);
                }

                $stmt->execute();
                $sql = "INSERT INTO $table (" . implode(',', $columnNames) . ") VALUES ";
                $firstRow = true;
            }
        }

        return count($rowsSQL);
    }

    /**
     * Obtiene el valor de la primera fila y la primera columna de los resultados de la consulta
     * @throws KpySqlException
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

    public function beginTransaction(): void
    {
        $this->initializeConnection();
        $this->link->beginTransaction();
    }

    public function commit(): void
    {
        $this->link->commit();
    }

    public function rollback(): void
    {
        $this->link->rollback();
    }

    public function prepare(string $sql, array $options = []): PDOStatement
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->link->prepare($sql, $options);
    }

    public function prepareForSelect(string $sql): PDOStatement
    {
        $this->initializeConnection();
        $this->lastSql = $sql;

        return $this->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }
}
