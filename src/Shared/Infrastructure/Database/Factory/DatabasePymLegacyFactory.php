<?php

namespace App\Shared\Infrastructure\Database\Factory;

use App\Shared\Infrastructure\Database\Database;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\DatabaseType;
use App\Shared\Infrastructure\Database\Decorator\DatabasePrefixerDecorator;

readonly class DatabasePymLegacyFactory implements DatabaseFactoryInterface
{
    public function __construct(
        private string $pymLegacyDSN,
        private string $pymLegacyUser,
        private string $pymLegacyPassword,
        private string $pymLegacyDbPrefix
    )
    {
    }

    public function create(): DatabaseInterface
    {
        $database = new Database($this->pymLegacyDSN, $this->pymLegacyUser, $this->pymLegacyPassword);
        return new DatabasePrefixerDecorator($database, $this->pymLegacyDbPrefix);
    }

    public function supports(array $context = []): bool
    {
        return ($context['name'] ?? 'undefined') === 'pym';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getDSN(): string
    {
        return $this->pymLegacyDSN;
    }

    public function getDatabaseType(): DatabaseType
    {
        return DatabaseType::MySQL;
    }
}
