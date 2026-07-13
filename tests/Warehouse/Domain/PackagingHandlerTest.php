<?php

namespace App\Tests\Warehouse\Domain;

use App\Warehouse\Domain\PackagingHandler;
use App\Warehouse\Domain\ValueObject\Package;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PackagingHandlerTest extends TestCase
{
    private PackagingHandler $packagingHandler;

    public function setUp(): void
    {
        $this->packagingHandler = new PackagingHandler([
            new Package('mediana', .5, 10),
            new Package('grande', 1, 20),
            new Package('peque', .2, 5),
        ]);
    }

    public function testSomething(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('weightCostProvider')]
    public function testCostByWeight(float $expected, float $weight): void
    {
        $this->assertEqualsWithDelta($expected, $this->packagingHandler->getCostFor($weight), .001);
    }

    public static function weightCostProvider(): iterable
    {
        yield [1, 20];
        yield [1, 12];
        yield [.5, 10];
        yield [.5, 6];
        yield [.2, 1];
        yield [1.2, 22];
        yield [2, 32];
        yield [1.5, 27];
        yield [2.2, 41];
        yield [3.5, 67];
        yield [0.2, 0];
    }
}
