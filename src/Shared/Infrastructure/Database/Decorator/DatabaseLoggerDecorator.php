<?php

namespace App\Shared\Infrastructure\Database\Decorator;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\DatabaseType;
use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerInterface;

readonly class DatabaseLoggerDecorator implements DatabaseInterface
{

    public function __construct(
        private DatabaseInterface $database,
        private LoggerInterface   $logger,
        private string            $databaseName,
        private DatabaseType      $type,
    )
    {
    }

    private function printHeaderSeparator(string $title = '', string $char = '-'): void
    {
        $this->logger->debug(
            str_repeat($char, 30) .
            str_pad($this->type->value . ' | ' . $title, 50, ' ', STR_PAD_BOTH) .
            str_repeat($char, 30)
        );

        $this->logger->debug(str_repeat($char, 110));
    }

    private function printFooterSeparator(string $char = '-'): void
    {
        $this->logger->debug(str_repeat($char, 110));
    }

    private function debugQuery(string $sql): void
    {
        $this->printHeaderSeparator("QUERY");
        $this->logger->debug($sql);
        $this->printFooterSeparator();
    }

    private function debugMakeConnection(): void
    {
        if (!$this->database->isConnected()) {
            $this->printHeaderSeparator('NUEVA CONEXIÓN REALIZADA', '*');
            $this->logger->debug('Connecting to: ' . $this->databaseName);
            $this->printFooterSeparator('*');
        }
    }

    public function execute(string $sql, int $mode = PDO::FETCH_ASSOC): bool|array
    {
        try {
            $this->debugMakeConnection();

            $result = $this->database->execute($sql, $mode);
            $this->debugQuery($this->getLastSql());

            return $result;

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;
        }
    }

    public function insert(string $table, array $data): bool
    {
        return $this->insertMany($table, $data);
    }

    public function insertMany(string $table, array $data, int $maxValueInsert = self::MAX_ROWS_BATCH): int
    {
        try {
            $this->debugMakeConnection();
            $result = $this->database->insertMany($table, $data, $maxValueInsert);
            $this->debugQuery($this->getLastSql());

            return $result;

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;
        }
    }

    public function getRow(string $sql): array
    {
        try {
            $this->debugMakeConnection();
            $result = $this->database->getRow($sql);
            $this->debugQuery($this->getLastSql());

            return $result;

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;
        }
    }

    public function getValue(string $sql): mixed
    {
        try {
            $this->debugMakeConnection();
            $result = $this->database->getValue($sql);
            $this->debugQuery($this->getLastSql());

            return $result;

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;
        }
    }

    public function beginTransaction(): void
    {
        try {
            $this->debugMakeConnection();
            $this->database->beginTransaction();

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;

        }
    }

    public function commit(): void
    {
        try {
            $this->database->commit();

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }

    public function rollback(): void
    {
        try {
            $this->database->rollback();

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }

    public function prepare(string $sql, array $options = []): PDOStatement
    {
        try {
            $this->debugMakeConnection();
            return $this->database->prepare($sql, $options);

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;

        }
    }

    public function prepareForSelect(string $sql): PDOStatement
    {
        try {
            $this->debugMakeConnection();
            return $this->database->prepareForSelect($sql);

        } catch (PDOException $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($this->database->getLastSql());

            throw $exception;

        }
    }

    public function isConnected(): bool
    {
        return $this->database->isConnected();
    }

    public function getSqlError(): string
    {
        return $this->database->getSqlError();
    }

    public function getLastSql(): string
    {
        return $this->database->getLastSql();
    }
}
