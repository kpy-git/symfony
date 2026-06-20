<?php

namespace App\Database\Factory;

use App\Database\Database;
use App\Database\DatabaseInterface;
use App\Database\DatabaseLoggerDecorator;
use App\Database\DatabaseType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class DatabaseAquaFactory implements DatabaseFactoryInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly string $aquaDSN,
        private readonly string $aquaUser,
        private readonly string $aquaPassword)
    {

    }

    public function create(): DatabaseInterface
    {
        $database = new Database(
            $this->aquaDSN,
            $this->aquaUser,
            $this->aquaPassword,
            []
        );

        if (strtolower($_ENV['LOG_DATABASE_QUERIES']) === 'enabled') {
            return new DatabaseLoggerDecorator(
                $database,
                $this->logger,
                $this->aquaDSN,
                DatabaseType::SQLServer
            );
        }

        return $database;
    }

    public function supports(array $context = []): bool
    {
        if (isset($context['name'])) {
            return $context['name'] === 'aqua';
        }

        return false;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
