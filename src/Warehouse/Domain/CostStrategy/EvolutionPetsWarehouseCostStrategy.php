<?php

namespace App\Warehouse\Domain\CostStrategy;

use App\Warehouse\Domain\ValueObject\Product;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class EvolutionPetsWarehouseCostStrategy implements WarehouseCostStrategyInterface
{

    public function __construct(
        #[Autowire('%kpy.warehouse.evolution_pets_fulfillment_cost%')]
        private float $manipulationCost,
    )
    {
    }

    public function computeManipulationCost(Product $product, int $quantity = 1): float
    {
        return $this->manipulationCost;
    }

    public function getType(): CostStrategyType
    {
        return CostStrategyType::EVOLUTION_PETS;
    }
}
