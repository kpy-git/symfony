<?php

namespace App\Database\Factory;

use App\Database\Database;
use App\Database\DatabaseInterface;
use App\Database\DatabaseType;
use App\Database\Decorator\DatabasePrefixerDecorator;

readonly class DatabaseKompyFactory implements DatabaseFactoryInterface
{
    public function __construct(
        private string $kompyDSN,
        private string $kompyDbUser,
        private string $kompyDbPassword,
        private string $kompyDbPrefix
    )
    {
    }

    public function create(): DatabaseInterface
    {
        $database = new Database($this->kompyDSN, $this->kompyDbUser, $this->kompyDbPassword);
        return new DatabasePrefixerDecorator($database, $this->kompyDbPrefix);
    }

    public function supports(array $context = []): bool
    {
        return ($context['name'] ?? 'undefined') === 'kompy';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getDSN(): string
    {
        return $this->kompyDSN;
    }

    public function getDatabaseType(): DatabaseType
    {
        return DatabaseType::MySQL;
    }
}
