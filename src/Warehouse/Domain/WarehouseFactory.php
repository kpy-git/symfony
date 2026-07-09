<?php

namespace App\Warehouse\Domain;

use App\Warehouse\Domain\CostStrategy\CostStrategyInterface;
use App\Warehouse\Domain\CostStrategy\FixedCommissionCostStrategy;
use App\Warehouse\Domain\CostStrategy\OwnershipCostStrategy;
use App\Warehouse\Domain\Exception\WarehouseException;
use App\Warehouse\Domain\Exception\WarehouseNotFoundException;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\BoskeFulfillmentCost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class WarehouseFactory
{
    private array $instances = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[AutowireIterator('kpy.warehouse.cost_strategy')]
        private readonly iterable $costStrategies
    )
    {
    }

    /**
     * @throws WarehouseException
     * @throws WarehouseNotFoundException
     */
    public function createFrom(int $warehouseId): Warehouse
    {
        if (!isset($this->instances[$warehouseId])) {
            /** @var \App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse $warehouseModel */
            $warehouseModel = $this->entityManager->getRepository(\App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse::class)->find($warehouseId);

            if (!$warehouseModel) {
                throw new WarehouseNotFoundException("Warehouse with id {$warehouseId} does not exist");
            }

            $warehouse = new Warehouse(
                $this->getCostStrategyByType($warehouseModel->getCostStrategyType()),
                $warehouseModel->getBoskeFulfillmentCost() ?? new BoskeFulfillmentCost(),
                $warehouseModel->isPackagingIncluded()
            );

            $this->instances[$warehouseId] = $warehouse;
        }

        return $this->instances[$warehouseId];
    }

    /**
     * @throws WarehouseException
     */
    private function getCostStrategyByType(CostStrategyType $type): CostStrategyInterface
    {
        /** @var CostStrategyInterface $costStrategy */
        foreach ($this->costStrategies as $costStrategy) {
            if ($type === $costStrategy->getType()) {
                return $costStrategy;
            }
        }

        throw new WarehouseException('No cost strategy available for ' . $type->value);
    }
}
