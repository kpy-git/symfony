<?php

namespace App\Database\Factory;

use App\Database\Database;

interface DatabaseFactoryInterface
{
    public function create(): Database;

    public function supports(array $context = []): bool;

    public function isActive(): bool;

    public function getLocatorKey(): string;
}
