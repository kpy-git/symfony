<?php

namespace App\Warehouse\Domain;

use App\Warehouse\Domain\CostStrategy\CostStrategyInterface;
use App\Warehouse\Domain\CostStrategy\FixedCommissionCostStrategy;
use App\Warehouse\Domain\CostStrategy\OwnWarehouseCostStrategy;
use App\Warehouse\Domain\Exception\WarehouseNotFoundException;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\BoskeFulfillmentCost;
use Doctrine\ORM\EntityManagerInterface;

class WarehouseFactory
{
    private array $instances = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    /**
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
                $this->getCostStrategyByWarehouse($warehouseModel),
                $warehouseModel->getBoskeFulfillmentCost() ?? new BoskeFulfillmentCost(),
                $warehouseModel->isPackagingIncluded()
            );

            $this->instances[$warehouseId] = $warehouse;
        }

        return $this->instances[$warehouseId];
    }

    private function getCostStrategyByWarehouse(\App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse $warehouse): CostStrategyInterface
    {
        if ($warehouse->getCommission() > 0) {
            return new FixedCommissionCostStrategy($warehouse->getCommission());
        }

        return new OwnWarehouseCostStrategy();
    }
}
