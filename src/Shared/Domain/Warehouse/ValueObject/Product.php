<?php

namespace App\Shared\Domain\Warehouse\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

readonly class Product
{
    public function __construct(
        private ProductCode $productCode,
        private int         $brand,
        private float       $weight,
        private float       $costPrice,
    )
    {
    }

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getBrand(): int
    {
        return $this->brand;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getCostPrice(): float
    {
        return $this->costPrice;
    }

    public function isBoske(): bool
    {
        return 178 === $this->brand;
    }
}
