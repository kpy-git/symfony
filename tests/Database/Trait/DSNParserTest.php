<?php

namespace App\Tests\Database\Trait;

use App\Database\Trait\DSNParser;
use PHPUnit\Framework\TestCase;

class DSNParserTest extends TestCase
{
    use DSNParser;

    public function testRunning(): void
    {
        $this->assertTrue(true);
    }

    public function testGetDatabaseNameFromSQLServerDSN(): void
    {
        $this->assertEquals('test', $this->getDatabaseNameFrom('sqlsrv:server=localhost;database=test'));
    }

    public function testGetDatabaseNameFromMySQLDSN(): void
    {
        $this->assertEquals('test', $this->getDatabaseNameFrom('mysql:host=localhost;dbname=test'));
    }

    public function testFailWhenUnknownDriverGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Driver no permitido');

        $this->getDatabaseNameFrom('pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass');

    }

    public function testFailNWhenInvalidDSNGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('DSN mal formado');

        $this->getDatabaseNameFrom('invalid-dsn;host=localhost;port=5432;dbname=test');
    }

    public function testParseParams(): void
    {
        $params = [
            'host' => 'localhost',
            'port' => '5432',
            'dbname' => 'testdb',
        ];

        $this->assertArraysAreEqual($params, $this->parseParams('host=localhost;port=5432;dbname=testdb'));
    }
}
