<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\CostStrategyType;
use App\Warehouse\Domain\PackagingHandler;
use App\Warehouse\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class OwnershipCostStrategy implements CostStrategyInterface
{

    public function __construct(
        #[Autowire('%kpy.warehouse.ownership_manipulation_cost%')]
        private float            $manipulationCost,
        private PackagingHandler $packagingHandler,
    )
    {
    }

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {
        return round(
            ($product->getCostPrice() * $quantity)
            + $this->manipulationCost
            + $this->packagingHandler->getCostFor($product->getWeight() * $quantity)
            , 6);
    }

    public function getType(): CostStrategyType
    {
        return CostStrategyType::OWNERSHIP;
    }
}
