<?php

namespace App\Tests\ShippingCostCalculator\Service;

use App\Shared\Domain\Aggregate\Destination;
use App\ShippingCostCalculator\Domain\Aggregate\Range;
use App\ShippingCostCalculator\Domain\Aggregate\RangeAdditionalPerKg;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{

    private Carrier $carrier;
    private CalculatorShippingCost $calculator;

    protected function setUp(): void
    {
        $this->carrier = new Carrier(1, 'mrw', 'service', 60);
        $this->carrier->setRangesByDestination(Destination::PENINSULA, [
            new Range(0, 5, 4),
            new Range(5.01, 10, 4.56),
            new Range(10.01, 15, 5.67),
            new Range(15.01, 20, 5.67),
            new Range(20.01, 25, 6.54),
            new Range(25.01, 30, 7.43),
            new Range(30.01, 35, 8.34),
            new Range(35.01, 40, 9.84),
            new RangeAdditionalPerKg(40.01, 0.5)
        ]);

        $this->calculator = new CalculatorShippingCost();
    }

    public function testRunning(): void
    {
        self::assertTrue(true);
    }

    #[DataProvider('peninsulaDataProvider')]
    public function testShippingCostByDestinationAndWeight(Destination $destination, float $weight, float $expected): void
    {

        $this->assertEqualsWithDelta(
            $expected,
            $this->calculator->getShippingCostBy($this->carrier, $destination, $weight),
            0.001
        );
    }

    public function testFailWhenMaxAllowedIsReached(): void
    {
        $this->expectException(ShippingCostException::class);
        $this->calculator->getShippingCostBy($this->carrier, Destination::PENINSULA, 100);
    }

    public function testFailWhenDestinationNotFound(): void
    {
        $this->expectException(ShippingCostException::class);
        $this->calculator->getShippingCostBy($this->carrier, Destination::BALEARES, 1);
    }

    public static function peninsulaDataProvider(): iterable
    {
        yield[Destination::PENINSULA, -1, 0];
        yield[Destination::PENINSULA, 0, 0];
        yield[Destination::PENINSULA, 1, 4];
        yield[Destination::PENINSULA, 3, 4];
        yield[Destination::PENINSULA, 6, 4.56];
        yield[Destination::PENINSULA, 10, 4.56];
        yield[Destination::PENINSULA, 11, 5.67];
        yield[Destination::PENINSULA, 23, 6.54];
        yield[Destination::PENINSULA, 28, 7.43];
        yield[Destination::PENINSULA, 30, 7.43];
        yield[Destination::PENINSULA, 33.5, 8.34];
        yield[Destination::PENINSULA, 45, 12.34];
    }
}
