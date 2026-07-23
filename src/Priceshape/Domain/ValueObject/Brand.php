<?php

namespace App\Priceshape\Domain\ValueObject;

readonly class Brand
{
    public function __construct(
        private int $manufacturerId,
        private bool $withFixedPrice,
    )
    {
    }

    public function getManufacturerId(): int
    {
        return $this->manufacturerId;
    }

    public function isWithFixedPrice(): bool
    {
        return $this->withFixedPrice;
    }

}
