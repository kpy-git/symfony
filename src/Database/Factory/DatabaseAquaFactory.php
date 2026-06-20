<?php

namespace App\Database\Factory;

use App\Database\Database;
use App\Database\DatabaseInterface;
use App\Database\DatabaseType;

class DatabaseAquaFactory implements DatabaseFactoryInterface
{


    public function __construct(
        private readonly string $aquaDSN,
        private readonly string $aquaUser,
        private readonly string $aquaPassword)
    {

    }

    public function create(): DatabaseInterface
    {
        return new Database(
            $this->aquaDSN,
            $this->aquaUser,
            $this->aquaPassword,
            []
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
