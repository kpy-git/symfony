<?php

declare(strict_types=1);

namespace App\Tests\Command\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Exception\RuntimeException;

class TestDatabaseCommandTest extends WebTestCase
{
    public function testCannotExecuteWithoutDatabaseArgument(): void
    {
        $this->expectException(RuntimeException::class);

        $result = static::runCommand('kpy:test:database');

        $this->assertStringContainsString('missing: "database-name"', $result->getOutput());
    }

    public function testCannotExecuteWithUnknownDatabaseArgument(): void
    {
        $result = static::runCommand('kpy:test:database', [
            'database-name' => 'invented'
        ]);

        $this->assertCommandFailed($result);
        $this->assertStringContainsString(
            'No se ha encontrado ninguna base de datos soportada',
            $result->getOutput()
        );
    }

    public function testExecuteAquaDatabase(): void
    {
        $result = static::runCommand('kpy:test:database', [
            'database-name' => 'aqua',
        ]);

        $this->assertStringContainsString('Conexión realizada correctamente', $result->getOutput());
        $this->assertCommandIsSuccessful($result);
    }

    public function testExecuteKompyDatabase(): void
    {
        $result = static::runCommand('kpy:test:database', [
            'database-name' => 'kompy',
        ]);

        $this->assertCommandIsSuccessful($result);
    }
}
