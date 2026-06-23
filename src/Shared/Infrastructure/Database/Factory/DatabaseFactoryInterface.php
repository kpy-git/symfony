<?php

namespace App\Shared\Infrastructure\Database\Factory;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\DatabaseType;

interface DatabaseFactoryInterface
{
    public function create(): DatabaseInterface;

    public function supports(array $context = []): bool;

    public function isActive(): bool;

    public function getDSN(): string;

    public function getDatabaseType(): DatabaseType;
}
