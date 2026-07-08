<?php

namespace App\Shared\Domain\Warehouse\CostStrategy;

use App\Shared\Domain\Warehouse\ValueObject\Product;

class NeftysCostStrategy implements CostStrategyInterface
{

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return round($product->getCostPrice() * 1.06 * $quantity, 6);
    }
}
