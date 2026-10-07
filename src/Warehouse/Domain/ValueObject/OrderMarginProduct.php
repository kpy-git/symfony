<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

class OrderMarginProduct
{
    public function __construct(
        private ProductCode $productCode,
        private int $quantity,
        private float $weight,
        private float $costPrice,
        private float $shippingCost = 0,
        private float $packingCost = 0,
        private float $manipulationCost = 0,
        private float $paymentCommission = 0,
    )
    {
    }

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCostPrice(): float
    {
        return round($this->costPrice, 6);
    }

    public function getShippingCost(): float
    {
        return round($this->shippingCost, 6);
    }

    public function getPackingCost(): float
    {
        return round($this->packingCost, 6);
    }

    public function getManipulationCost(): float
    {
        return round($this->manipulationCost, 6);
    }

    public function getPaymentCommission(): float
    {
        return round($this->paymentCommission, 6);
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setManipulationCost(float $cost): self
    {
        $this->manipulationCost = $cost;
        return $this;
    }

    public function setPackingCost(float $packingCost): self
    {
        $this->packingCost = $packingCost;
        return $this;
    }

    public function setPaymentCommission(float $paymentCommission): self
    {
        $this->paymentCommission = $paymentCommission;
        return $this;
    }

    public function setShippingCost(float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;
        return $this;
    }

}
