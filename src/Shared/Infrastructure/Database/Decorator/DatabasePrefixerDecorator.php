<?php

namespace App\Shared\Infrastructure\Database\Decorator;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use PDO;
use PDOStatement;

readonly class DatabasePrefixerDecorator implements DatabaseInterface
{
    public function __construct(
        private DatabaseInterface $database,
        private string $dbPrefix
    )
    {
    }

    private function setDatabasePrefix(string $sql): string
    {
        return str_replace('ps_', $this->dbPrefix, $sql);
    }

    public function isConnected(): bool
    {
        return $this->database->isConnected();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $sql, int $mode = PDO::FETCH_ASSOC): bool|array
    {
        return $this->database->execute($this->setDatabasePrefix($sql), $mode);
    }

    /**
     * @inheritDoc
     */
    public function getRow(string $sql): array
    {
        return $this->database->getRow($this->setDatabasePrefix($sql));
    }

    /**
     * @inheritDoc
     */
    public function insert(string $table, array $data): bool
    {
        return $this->database->insert($this->dbPrefix . $table, $data);
    }

    /**
     * @inheritDoc
     */
    public function insertMany(string $table, array $data, int $maxValueInsert = self::MAX_ROWS_BATCH): int
    {
        return $this->database->insertMany($this->dbPrefix . $table, $data);
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $sql): mixed
    {
        return $this->database->getValue($this->setDatabasePrefix($sql));
    }

    public function getSqlError(): string
    {
        return $this->database->getSqlError();
    }

    public function getLastSql(): string
    {
        return $this->database->getLastSql();
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->database->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->database->commit();
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        $this->database->rollback();
    }

    /**
     * @inheritDoc
     */
    public function prepare(string $sql, array $options = []): PDOStatement
    {
        return $this->database->prepare($this->setDatabasePrefix($sql), $options);
    }

    /**
     * @inheritDoc
     */
    public function prepareForSelect(string $sql): PDOStatement
    {
        return $this->database->prepareForSelect($this->setDatabasePrefix($sql));
    }
}
