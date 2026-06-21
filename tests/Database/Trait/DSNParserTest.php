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

    public function testGetDSNWhenUnknownDriverGiven(): void
    {
        $this->assertEquals(
            'pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass',
            $this->getDatabaseNameFrom('pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass')
        );
    }

    public function testGetDSNWhenInvalidDSNGiven(): void
    {
        $this->assertEquals(
            'invalid-dsn;host=localhost;port=5432;dbname=test',
            $this->getDatabaseNameFrom('invalid-dsn;host=localhost;port=5432;dbname=test')
        );
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
