<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\ValueObject\Product;

interface CostStrategyInterface
{
    public function computeFinalCostPrice(Product $product, int $quantity = 1): float;
}
