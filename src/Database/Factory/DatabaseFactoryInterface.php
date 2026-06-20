<?php

namespace App\Database\Factory;

use App\Database\DatabaseInterface;

interface DatabaseFactoryInterface
{
    public function create(): DatabaseInterface;

    public function supports(array $context = []): bool;

    public function isActive(): bool;

}
