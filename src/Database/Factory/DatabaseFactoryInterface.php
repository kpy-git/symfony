<?php

namespace App\Database\Factory;

use App\Database\DatabaseInterface;
use App\Database\DatabaseType;

interface DatabaseFactoryInterface
{
    public function create(): DatabaseInterface;

    public function supports(array $context = []): bool;

    public function isActive(): bool;

    public function getDSN(): string;

    public function getDatabaseType(): DatabaseType;
}
