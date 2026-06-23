<?php

namespace App\ShippingCostCalculator\Domain\Aggregate;

class RangeAdditionalPerKg implements RangeInterface
{
    private float $fromWeight;

    private float $costPerKg;

    public function __construct(float $fromWeight, float $costPerKg)
    {
        $this->fromWeight = $fromWeight;
        $this->costPerKg = $costPerKg;
    }

    public function getCost(): float
    {
        return $this->costPerKg;
    }

    public function isWeightAllowed(float $weight): bool
    {
        return $this->fromWeight >= $weight;
    }

    public function getFromWeight(): float
    {
        return $this->fromWeight;
    }

}
