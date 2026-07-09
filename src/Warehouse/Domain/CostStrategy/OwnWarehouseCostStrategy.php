<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\ValueObject\Product;

class OwnWarehouseCostStrategy implements CostStrategyInterface
{

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return round($product->getCostPrice() * $quantity, 6);
    }
}
