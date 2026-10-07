<?php

namespace App\Warehouse\Domain\ValueObject;

class OrderMargin
{
    /** @var OrderMarginProduct[] */
    private array $products;

    private float $totalProductsCost;
    private float $totalShippingCost;
    private float $totalPackingCost;
    private float $totalManipulationCost;

    public function __construct(
        private readonly int   $orderId,
        private readonly float $totalPaid
    )
    {
        $this->products = [];
    }

    public function addProduct(OrderMarginProduct $product): void
    {
        $this->products[] = $product;
    }

    public function getMarginPercentage(): float
    {
        if ($this->totalPaid <= 0) {
            return 0;
        }

        return round($this->getMarginAmount() * 100 / $this->totalPaid, 2);
    }

    public function getMarginAmount(): float
    {
        return round($this->totalPaid
            - $this->totalProductsCost
            - $this->totalManipulationCost
            - $this->totalPackingCost
            - $this->totalShippingCost, 6);
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    public function getTotalProductsCost(): float
    {
        return $this->totalProductsCost;
    }

    public function setTotalProductsCost(float $totalProductsCost): self
    {
        $this->totalProductsCost = $totalProductsCost;
        return $this;
    }

    public function getTotalShippingCost(): float
    {
        return $this->totalShippingCost;
    }

    public function setTotalShippingCost(float $totalShippingCost): self
    {
        $this->totalShippingCost = $totalShippingCost;
        return $this;
    }

    public function getTotalPackingCost(): float
    {
        return $this->totalPackingCost;
    }

    public function setTotalPackingCost(float $totalPackingCost): self
    {
        $this->totalPackingCost = $totalPackingCost;
        return $this;
    }

    public function getTotalManipulationCost(): float
    {
        return $this->totalManipulationCost;
    }

    public function setTotalManipulationCost(float $totalManipulationCost): self
    {
        $this->totalManipulationCost = $totalManipulationCost;
        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getTotalPaid(): float
    {
        return $this->totalPaid;
    }

}
