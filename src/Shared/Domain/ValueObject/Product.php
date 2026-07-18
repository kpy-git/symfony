<?php

namespace App\Shared\Domain\ValueObject;

readonly class Product
{
    public function __construct(
        private ProductCode $productCode,
        private float $weight,
        private int $brandId,
        private bool $pack,
        private bool $jirafa,
    )
    {
    }

    /**
     * @return ProductCode
     */
    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getBrandId(): int
    {
        return $this->brandId;
    }

    public function isJirafa(): bool
    {
        return $this->jirafa;
    }

    public function isPack(): bool
    {
        return $this->pack;
    }
}
