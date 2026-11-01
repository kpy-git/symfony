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
        if (filter_var($dsn, FILTER_VALIDATE_URL)) {
            return $this->parseURL($dsn)['database'];
        }

        if (!str_contains($dsn, ':')) {
            throw new \InvalidArgumentException('DSN mal formado');
        }

        [$driver, $stringConnection] = explode(':', $dsn, 2);

        if (!in_array($driver, $this->acceptedDSNs)) {
            throw new \InvalidArgumentException('Driver no permitido, valores válidos: ' . implode(', ', $this->acceptedDSNs));
        }

        $parsedParams = $this->parseDSN($stringConnection);

        return match ($driver) {
            'sqlsrv' => $parsedParams['database'],
            'mysql' => $parsedParams['dbname'],
            'sqlite' => $stringConnection[0],
            default => $dsn,
        };
    }

    private function parseDSN(string $dsn): array
    {
        $parsedParams = [];
        $params = explode(';', $dsn);
        foreach ($params as $param) {
            [$key, $value] = explode('=', $param);
            $parsedParams[trim($key)] = trim($value);
        }

        return $parsedParams;
    }

    private function parseURL(string $url): array
    {
        // $url = 'mysql://app_user:app_passwd@app_host:3306/app_db?serverVersion=8.4.9&charset=utf8mb4';
        $parts = parse_url($url);

        return [
            'user' => $parts['user'],
            'pass' => $parts['pass'],
            'driver' => $parts['host'],
            'port' => $parts['port'],
            'database' => ltrim($parts['path'], '/'),
        ];
    }
}
