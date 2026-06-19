<?php

namespace App\Database\Factory;

use App\Database\Database;
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

    public function create(): Database
    {
        $database = new DatabaseLoggerDecorator(
            $this->aquaDSN,
            $this->aquaUser,
            $this->aquaPassword,
            []
        );

        return $database
            ->setLogger($this->logger)
            ->setType(DatabaseType::SQLServer);
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

    public function getLocatorKey(): string
    {
        return $this->aquaDSN;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
