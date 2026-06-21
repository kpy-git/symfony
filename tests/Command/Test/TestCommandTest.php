<?php

declare(strict_types=1);

namespace App\Tests\Command\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCommandTest extends WebTestCase
{
    public function testExecute(): void
    {
        $result = self::runCommand('kpy:test');

        $this->assertCommandIsSuccessful($result);
    }
}
