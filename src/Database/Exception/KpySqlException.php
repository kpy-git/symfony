<?php

namespace App\Database\Exception;

class KpySqlException extends \Exception
{
    public function __construct(string $message,
                                private readonly string $method,
                                private readonly string $lastSql,
                                private readonly string $sqlError)
    {
        parent::__construct($message);
    }

    public function getLastSql(): string
    {
        return $this->lastSql;
    }

    public function getSqlError(): string
    {
        return $this->sqlError;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function __toString(): string
    {
        return implode("\n", [
            $this->message,
            $this->method,
            $this->lastSql,
            $this->sqlError,
        ]);
    }
}
