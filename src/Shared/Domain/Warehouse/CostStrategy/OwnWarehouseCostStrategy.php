<?php

namespace App\Shared\Domain\Warehouse\CostStrategy;

use App\Shared\Domain\Warehouse\CostStrategy\CostStrategyInterface;
use App\Shared\Domain\Warehouse\ValueObject\Product;

class OwnWarehouseCostStrategy implements CostStrategyInterface
{

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return round($product->getCostPrice() * $quantity, 6);
    }
}
