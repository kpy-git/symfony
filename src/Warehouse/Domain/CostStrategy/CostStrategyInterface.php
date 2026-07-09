<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\CostStrategyType;
use App\Warehouse\Domain\PackagingHandler;
use App\Warehouse\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.cost_strategy')]
interface CostStrategyInterface
{
    public function computeFinalCostPrice(Product $product, int $quantity = 1): float;

    public function getType(): CostStrategyType;
}
