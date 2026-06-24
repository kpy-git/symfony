<?php

namespace App\Shared\Infrastructure\Database\Factory;

use App\Shared\Infrastructure\Database\Database;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\DatabaseType;

readonly class DatabaseDoctrine implements DatabaseFactoryInterface
{
    public function __construct(
        private string $doctrineDSN,
        private string $doctrineUser,
        private string $doctrinePassword
    )
    {
    }

    public function create(): DatabaseInterface
    {
        return new Database($this->doctrineDSN, $this->doctrineUser, $this->doctrinePassword);
    }

    public function supports(array $context = []): bool
    {
        return strtolower($context['name'] ?? 'unknown') === 'doctrine';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getDSN(): string
    {
        return $this->doctrineDSN;
    }

    public function getDatabaseType(): DatabaseType
    {
        return DatabaseType::MySQL;
    }
}
