<?php

namespace App\ShippingCostCalculator\Domain\Aggregate;

interface RangeInterface
{
    public function getCost(): float;

    public function isWeightAllowed(float $weight): bool;
}
