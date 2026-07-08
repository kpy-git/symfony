<?php

namespace App\Shared\Domain\Warehouse\CostStrategy;

use App\Shared\Domain\Warehouse\ValueObject\Product;

interface CostStrategyInterface
{
    public function computeFinalCostPrice(Product $product, int $quantity = 1): float;
}
