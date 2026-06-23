<?php

namespace App\Shared\Infrastructure\Database\Factory;

use App\Shared\Infrastructure\Database\Database;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\DatabaseType;

readonly class DatabaseAquaFactory implements DatabaseFactoryInterface
{


    public function __construct(
        private string $aquaDSN,
        private string $aquaUser,
        private string $aquaPassword)
    {

    }

    public function create(): DatabaseInterface
    {
        return new Database(
            $this->aquaDSN,
            $this->aquaUser,
            $this->aquaPassword
        );
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


    public function getDatabaseType(): DatabaseType
    {
        return DatabaseType::SQLServer;
    }

    public function getDSN(): string
    {
        return $this->aquaDSN;
    }
}
