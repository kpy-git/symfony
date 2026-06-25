<?php

declare(strict_types=1);

namespace App\Tests\ShippingCostCalculator\Domain;

use App\Shared\Domain\Destination;
use App\ShippingCostCalculator\Domain\Aggregate\Range;
use App\ShippingCostCalculator\Domain\Aggregate\RangeAdditionalPerKg;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CarrierTest extends WebTestCase
{
    private $carrier;

    protected function setUp(): void
    {
        $this->carrier = new Carrier(1, 'mrw', 'SERVICE', 60);
    }

    public function testRunning(): void
    {
        $this->assertTrue(true);
    }

    public function testFailRangesWithUnownedDestination(): void
    {
        $this->expectException(ShippingCostException::class);

        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());

        $this->carrier->getRangesByDestination(Destination::BALEARES);
    }

    public function testGetRangesByDestination(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, [
            new Range(35.01, 40, 9.84),
            new RangeAdditionalPerKg(40.01, .5),
        ]);

        $this->assertArraysAreEqual(
            [new Range(35.01, 40, 9.84)],
            $this->carrier->getrangesByDestination(Destination::PENINSULA),
        );
    }

    public function testHasAdditionalCostPerKg(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());

        $this->assertTrue($this->carrier->hasAdditionalCostPerKgByDestination(Destination::PENINSULA));
    }

    public function testCorrectRangeAdditionalCostPerKg(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());

        $this->assertEquals(
            new RangeAdditionalPerKg(40.01, .5),
            $this->carrier->getRangeAdditionalPerKgByDestination(Destination::PENINSULA)
        );
    }

    public function testNullableRangeAdditionalCostPerKg(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, [
            new Range(0, 5, 4),
        ]);

        $this->assertEquals(
            null,
            $this->carrier->getRangeAdditionalPerKgByDestination(Destination::PENINSULA)
        );
    }

    public function testGetHighestRangeCostByDestination(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());

        $this->assertEqualsWithDelta(9.84, $this->carrier->getHighestRangeCostByDestination(Destination::PENINSULA), .001);
    }

    public function testCostAdditionalPerKg(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());

        $this->assertEqualsWithDelta(0.5, $this->carrier->getCostAdditionalPerKg(Destination::PENINSULA), .001);
    }

    public function testFailGetHighestRangeCostByDestination(): void
    {
        $this->expectException(ShippingCostException::class);

        $this->carrier->setRangesByDestination(Destination::PENINSULA, []);
        $this->carrier->gethighestRangeCostByDestination(Destination::PENINSULA);
    }

    public function testFailCostAdditionalPerKg(): void
    {
        $this->expectException(ShippingCostException::class);

        $this->carrier->setRangesByDestination(Destination::PENINSULA, []);
        $this->carrier->getCostAdditionalPerKg(Destination::PENINSULA);
    }

    public function testGetInitWeightAdditionalPerKg(): void
    {
        $this->carrier->setRangesByDestination(Destination::PENINSULA, $this->getMocksRange());
        $this->assertEqualsWithDelta(40.01, $this->carrier->getinitWeightAdditionalPerKg(Destination::PENINSULA), 0.001);
    }

    public function getMocksRange(): array
    {
        return [
            new Range(0, 5, 4),
            new Range(5.01, 10, 4.56),
            new Range(10.01, 15, 5.67),
            new Range(15.01, 20, 5.67),
            new Range(20.01, 25, 6.54),
            new Range(25.01, 30, 7.43),
            new Range(30.01, 35, 8.34),
            new Range(35.01, 40, 9.84),
            new RangeAdditionalPerKg(40.01, .5)
        ];
    }
}
