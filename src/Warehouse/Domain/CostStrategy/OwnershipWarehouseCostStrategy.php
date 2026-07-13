<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class OwnershipWarehouseCostStrategy implements WarehouseCostStrategyInterface
{

    public function __construct(
        #[Autowire('%kpy.warehouse.ownership_manipulation_cost%')]
        private float            $manipulationCost,
    )
    {
    }

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return ($product->getCostPrice() * $quantity) + $this->manipulationCost;
    }

    public function getType(): CostStrategyType
    {
        return CostStrategyType::OWNERSHIP;
    }
}
