<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.cost_strategy')]
interface WarehouseCostStrategyInterface
{
    public function computeFinalCostPrice(Product $product, int $quantity = 1): float;

    public function getType(): CostStrategyType;
}
