<?php

namespace App\Shared\Domain\Warehouse;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Warehouse\CostStrategy\CostStrategyInterface;
use App\Shared\Domain\Warehouse\CostStrategy\FixedCommissionCostStrategy;
use App\Shared\Domain\Warehouse\CostStrategy\OwnWarehouseCostStrategy;
use App\Shared\Domain\Warehouse\ValueObject\BoskeFulfillmentCost;
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
     * @throws KpyException
     */
    public function createFrom(int $warehouseId): Warehouse
    {
        if (!isset($this->instances[$warehouseId])) {
            /** @var \App\Shared\Infrastructure\Persistence\Doctrine\Entity\Warehouse $warehouseModel */
            $warehouseModel = $this->entityManager->getRepository(\App\Shared\Infrastructure\Persistence\Doctrine\Entity\Warehouse::class)->find($warehouseId);

            if (!$warehouseModel) {
                throw new KpyException("Warehouse with id {$warehouseId} does not exist");
            }

            $warehouse = new Warehouse(
                $this->getCostStrategyByWarehouse($warehouseModel),
                new BoskeFulfillmentCost(
                    2.5,
                    1,
                    4.5,
                    1
                )
            );

            $this->instances[$warehouseId] = $warehouse;
        }

        return $this->instances[$warehouseId];
    }

    /**
     * @throws KpyException
     */
    private function getCostStrategyByWarehouse(\App\Shared\Infrastructure\Persistence\Doctrine\Entity\Warehouse $warehouse): CostStrategyInterface
    {
        if ($warehouse->getCommission() > 0) {
            return new FixedCommissionCostStrategy($warehouse->getCommission());
        }

        return new OwnWarehouseCostStrategy();
    }
}
