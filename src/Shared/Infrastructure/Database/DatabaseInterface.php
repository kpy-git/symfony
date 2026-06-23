<?php

namespace App\Shared\Infrastructure\Database;

use PDO;
use PDOException;
use PDOStatement;

interface DatabaseInterface
{
    public const int MAX_ROWS_BATCH = 500;

    public function isConnected(): bool;

    /**
     * @throws PDOException
     */
    public function execute(string $sql, int $mode = PDO::FETCH_ASSOC): bool|array;

    /**
     * @throws PDOException
     */
    public function getRow(string $sql): array;

    /**
     * @throws PDOException
     */
    public function insert(string $table, array $data): bool;

    /**
     * @throws PDOException
     */
    public function insertMany(string $table, array $data, int $maxValueInsert = self::MAX_ROWS_BATCH): int;

    /**
     * @throws PDOException
     */
    public function getValue(string $sql): mixed;

    public function getSqlError(): string;

    public function getLastSql(): string;

    /**
     * @throws PDOException
     */
    public function beginTransaction(): void;

    /**
     * @throws PDOException
     */
    public function commit(): void;

    /**
     * @throws PDOException
     */
    public function rollback(): void;

    /**
     * @throws PDOException
     */
    public function prepare(string $sql, array $options = []): PDOStatement;

    /**
     * @throws PDOException
     */
    public function prepareForSelect(string $sql): PDOStatement;
}
