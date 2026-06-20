<?php

namespace App\Database\Trait;

trait DSNParser
{
    private array $acceptedDSNs = [
        'sqlsrv',
        'mysql',
        'sqlite',
    ];

    public function getDatabaseFrom(string $dsn): string
    {
        if (!str_contains($dsn, ':')) {
            return $dsn;
        }

        [$driver, $params] = explode(':', $dsn);

        if (!in_array($driver, $this->acceptedDSNs)) {
            return $dsn;
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
