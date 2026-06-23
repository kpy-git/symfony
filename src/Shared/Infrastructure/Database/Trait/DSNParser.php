<?php

namespace App\Shared\Infrastructure\Database\Trait;

trait DSNParser
{
    private array $acceptedDSNs = [
        'sqlsrv',
        'mysql',
        'sqlite',
    ];

    public function getDatabaseNameFrom(string $dsn): string
    {
        if (!str_contains($dsn, ':')) {
            throw new \InvalidArgumentException('DSN mal formado');
        }

        [$driver, $params] = explode(':', $dsn);

        if (!in_array($driver, $this->acceptedDSNs)) {
            throw new \InvalidArgumentException('Driver no permitido, valores válidos: ' . implode(', ', $this->acceptedDSNs));
        }

        $parsedParams = $this->parseParams($params);

        return match ($driver) {
            'sqlsrv' => $parsedParams['database'],
            'mysql' => $parsedParams['dbname'],
            'sqlite' => $params[0],
            default => $dsn,
        };
    }

    private function parseParams(string $params): array
    {
        $parsedParams = [];
        $parts = explode(';', $params);
        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part);
            $parsedParams[trim($key)] = trim($value);
        }

        return $parsedParams;
    }

}
