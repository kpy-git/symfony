<?php

namespace App\Warehouse\Domain\ValueObject;

readonly class Package
{
    public function __construct(
        private string $name,
        private float $cost,
        private float $maxWeightAllowed,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getMaxWeightAllowed(): float
    {
        return $this->maxWeightAllowed;
    }

}
