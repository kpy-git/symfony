<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

readonly class WarehouseProductFulfillmentCost
{
    public function __construct(
        private ProductCode $productCode,
        private int $warehouseId,
        private float $fulfillmentCost,
    )
    {
    }

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getFulfillmentCost(): float
    {
        return $this->fulfillmentCost;
    }

}
