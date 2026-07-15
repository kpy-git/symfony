<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

readonly class OrderProduct
{
    public function __construct(
        private ProductCode $productCode,
        private string $name,
        private int $quantity,
        private string $ean,
        private float $weight,
    )
    {
    }

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function equals(self $orderProduct): bool
    {
        return $this->productCode->equals($orderProduct->productCode);
    }
}
