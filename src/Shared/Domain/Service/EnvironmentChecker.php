<?php

namespace App\Shared\Domain\Service;

readonly class EnvironmentChecker
{
    public function __construct(private string $environment)
    {
    }

    public function isProduction(): bool
    {
        return $this->environment === 'prod';
    }

    public function isTest(): bool
    {
        return $this->environment === 'test';
    }

    public function isDevelopment(): bool
    {
        return $this->environment === 'dev';
    }
}
