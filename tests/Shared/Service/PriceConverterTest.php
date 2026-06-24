<?php

declare(strict_types=1);

namespace App\Tests\Shared\Service;

use App\Shared\Domain\Service\PriceConverter;
use PHPUnit\Framework\TestCase;

class PriceConverterTest extends TestCase
{
    public function testToIntegerWithTwoDecimals(): void
    {
        $priceConverter = new PriceConverter();

        $this->assertSame(12550, $priceConverter->toInteger('125.50'));
        $this->assertSame(1500, $priceConverter->toInteger('15.0'));
        $this->assertSame(50, $priceConverter->toInteger('.5'));
        $this->assertSame(0, $priceConverter->toInteger('0.0'));
    }

    public function testToIntegerWithThreeDecimals(): void
    {
        $priceConverter = new PriceConverter(3);

        $this->assertSame(125523, $priceConverter->toInteger('125.52314'));
        $this->assertSame(15767, $priceConverter->toInteger('15.7678'));
        $this->assertSame(545, $priceConverter->toInteger('0.5456'));
        $this->assertSame(0, $priceConverter->toInteger('0.0'));
    }

    public function testToIntegerWithSixDecimals(): void
    {
        $priceConverter = new PriceConverter(6);

        $this->assertSame(125523142, $priceConverter->toInteger('125.5231423124'));
        $this->assertSame(15761278, $priceConverter->toInteger('15.76127813'));
        $this->assertSame(545600, $priceConverter->toInteger('0.5456'));
        $this->assertSame(0, $priceConverter->toInteger('0.0'));
    }

    public function testIntegerToDecimal(): void
    {
        $priceConverter = new PriceConverter();

        $this->assertEqualsWithDelta(125.5, $priceConverter->toDecimal(12550), 0.001);
        $this->assertEqualsWithDelta(12513.5, $priceConverter->toDecimal(1251350), 0.001);
        $this->assertEqualsWithDelta(125.55, $priceConverter->toDecimal(12555), 0.001);
        $this->assertEqualsWithDelta(0, $priceConverter->toDecimal(0), 0.001);
        $this->assertEqualsWithDelta(5, $priceConverter->toDecimal(500), 0.001);
        $this->assertEqualsWithDelta(0.1, $priceConverter->toDecimal(10), 0.001);
        $this->assertEqualsWithDelta(0.01, $priceConverter->toDecimal(1), 0.001);
    }

    public function testIntegerToDecimalWithSixDecimals(): void
    {
        $priceConverter = new PriceConverter(6);

        $this->assertEqualsWithDelta(125.587352, $priceConverter->toDecimal(125587352), 0.001);
        $this->assertEqualsWithDelta(125.55, $priceConverter->toDecimal(125550000), 0.001);
        $this->assertEqualsWithDelta(0, $priceConverter->toDecimal(0), 0.001);
        $this->assertEqualsWithDelta(5, $priceConverter->toDecimal(5000000), 0.001);
        $this->assertEqualsWithDelta(0.1, $priceConverter->toDecimal(100000), 0.001);
        $this->assertEqualsWithDelta(0.01, $priceConverter->toDecimal(10000), 0.001);
    }
}
