<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\Destination;
use App\ShippingCostCalculator\Domain\Carrier;
use App\Warehouse\Domain\CostStrategy\WarehouseCostStrategyInterface;
use App\Warehouse\Domain\ValueObject\Product;

class Warehouse
{
    public function __construct(
        private readonly WarehouseCostStrategyInterface $costStrategy,
        private readonly Carrier                        $carrier,
        private readonly PackagingHandler               $packagingHandler,
    )
    {
    }

    public function getProductCostPrice(Product $product, int $quantity = 1): float
    {
        return round($this->costStrategy->computeFinalCostPrice($product, $quantity), 6);
    }

    public function getCarrier(): ?Carrier
    {
        return $this->carrier;
    }

    public function getDefaultDestination(): Destination
    {
        return Destination::PENINSULA;
    }

    public function getPackagingHandler(): PackagingHandler
    {
        return $this->packagingHandler;
    }
}
