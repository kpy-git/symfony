<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\CostStrategy\CostStrategyInterface;
use App\Warehouse\Domain\CostStrategyType;
use App\Warehouse\Domain\PackagingHandler;
use App\Warehouse\ValueObject\Product;

class AbstractCostStrategy implements CostStrategyInterface
{
    protected PackagingHandler $packagingHandler;

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        // TODO: Implement computeFinalCostPrice() method.
    }

    public function getType(): CostStrategyType
    {
        // TODO: Implement getType() method.
    }

    public function addPackagingHandler(PackagingHandler $packagingHandler): void
    {
        $this->packagingHandler = $packagingHandler;
    }
}
