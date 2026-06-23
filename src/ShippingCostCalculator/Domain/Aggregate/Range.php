<?php

namespace App\ShippingCostCalculator\Domain\Aggregate;

use App\Shared\Service\PriceConverter;

class Range implements RangeInterface
{

    private int $minWeight;

    private int $maxWeight;

    private int $cost;

    private PriceConverter $priceConverter;

    public function __construct(float $minWeight, float $maxWeight, float $cost)
    {
        $this->priceConverter = new PriceConverter();

        $this->minWeight = $this->priceConverter->toInteger($minWeight);
        $this->maxWeight = $this->priceConverter->toInteger($maxWeight);
        $this->cost = $this->priceConverter->toInteger($cost);
    }

    public function getCost(): float
    {
        return $this->priceConverter->toDecimal($this->cost);
    }

    public function isWeightAllowed(float $weight): bool
    {
        $weightInt = $this->priceConverter->toInteger($weight);
        return $weightInt > $this->minWeight && $weightInt <= $this->maxWeight;
    }
}
