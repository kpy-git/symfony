<?php

namespace App\Database;

use PDO;
use Psr\Log\LoggerInterface;

class DatabaseLoggerDecorator extends Database
{
    private LoggerInterface $logger;

    private DatabaseType $type;

    public function __construct(
        private readonly string $dsn,
        string                  $username,
        string                  $password,
        array                   $options = []
    )
    {
        parent::__construct($dsn, $username, $password, $options);
    }

    protected function connect(): void
    {
        if ($this->link !== null) {
            return;
        }

        $this->printHeaderSeparator('NUEVA CONEXIÓN REALIZADA', '*');
        $this->logger->debug('Connecting to: ' . $this->dsn);
        $this->printFooterSeparator('*');

        parent::connect();
    }

    private function printHeaderSeparator(string $category = '', string $char = '-'): void
    {
        $this->logger->debug(
            str_repeat($char, 30) .
            str_pad($this->type->value . ' | ' . $category, 40, ' ', STR_PAD_BOTH) .
            str_repeat($char, 30)
        );
    }

    public function printFooterSeparator(string $char = '-'): void
    {
        $this->logger->debug(str_repeat($char, 100));
    }

    public function execute(string $sql, int $mode = PDO::FETCH_ASSOC): bool|array
    {
        $result = parent::execute($sql, $mode);
        $this->debugQuery($this->getLastSql());

        return $result;
    }

    public function debugQuery(string $sql): void
    {
        $this->printHeaderSeparator("NUEVA CONSULTA");
        $this->logger->debug($sql);
        $this->printHeaderSeparator();
    }

    public function insertMany(string $table, array $data, int $maxValueInsert = self::MAX_ROWS_BATCH): int
    {
        $result = parent::insertMany($table, $data, $maxValueInsert);
        $this->debugQuery($this->getLastSql());

        return $result;
    }

    public function getRow(string $sql): array
    {
        $result = parent::getRow($sql);
        $this->debugQuery($this->getLastSql());

        return $result;
    }

    public function getValue(string $sql): mixed
    {
        $result = parent::getValue($sql);
        $this->debugQuery($this->getLastSql());

        return $result;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setType(DatabaseType $type): self
    {
        $this->type = $type;
        return $this;
    }
}
