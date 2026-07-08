<?php

namespace App\Shared\Domain\Warehouse\CostStrategy;

use App\Shared\Domain\Warehouse\ValueObject\Product;

class FixedCommissionCostStrategy implements CostStrategyInterface
{
    private float $commission;

    public function __construct(float $commissionPercentage)
    {
        $this->commission = 1 + ($commissionPercentage / 100);
    }

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return round($product->getCostPrice() * $this->commission * $quantity, 6);
    }
}
