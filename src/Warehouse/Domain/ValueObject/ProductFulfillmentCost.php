<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

readonly class ProductFulfillmentCost implements \JsonSerializable
{
    public function __construct(
        private ProductCode $productCode,
        private float $productCost,
        private float $manipulationCost,
        private float $shippingCost,
        private float $packagingCost
    )
    {
    }

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getProductCost(): float
    {
        return $this->productCost;
    }

    public function getManipulationCost(): float
    {
        return $this->manipulationCost;
    }

    public function getShippingCost(): float
    {
        return $this->shippingCost;
    }

    public function getPackagingCost(): float
    {
        return $this->packagingCost;
    }

    public function getTotalCost(): float
    {
        return round($this->productCost + $this->manipulationCost + $this->shippingCost + $this->packagingCost, 6);
    }

    public function jsonSerialize(): array
    {
        return [
            'sku' => $this->productCode->getSku(),
            'cost' => $this->productCost,
            'manipulation_cost' => $this->manipulationCost,
            'shipping_cost' => $this->shippingCost,
            'packaging_cost' => $this->packagingCost,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->getTotalCost();
    }
}
