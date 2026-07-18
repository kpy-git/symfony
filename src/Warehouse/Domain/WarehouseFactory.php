<?php

namespace App\Warehouse\Domain;

use App\ShippingCostCalculator\Domain\Builder\CarrierBuilder;
use App\Warehouse\Domain\CostStrategy\CostStrategyType;
use App\Warehouse\Domain\CostStrategy\WarehouseCostStrategyInterface;
use App\Warehouse\Domain\Exception\WarehouseException;
use App\Warehouse\Domain\Exception\WarehouseNotFoundException;
use App\Warehouse\Domain\ValueObject\Package;
use App\Warehouse\Infrastructure\API\KpyPublicApi;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\PackageModel;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class WarehouseFactory
{
    private array $instances = [];

    public function __construct(
        private readonly EntityManagerInterface     $entityManager,
        #[AutowireIterator('kpy.warehouse.cost_strategy')]
        private readonly iterable                   $costStrategies,
        private readonly CarrierBuilder             $carrierBuilder,
        private readonly KpyPublicApi               $kpyPublicApi,
        private readonly WarehouseProductRepository $warehouseProductRepository,
    )
    {
    }

    /**
     * @throws WarehouseNotFoundException
     */
    public function createFrom(string $warehouseName): Warehouse
    {
        /** @var \App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse $warehouseModel */
        $warehouseModel = $this->entityManager->getRepository(\App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse::class)->findOneBy(['name' => $warehouseName]);
        if (!$warehouseModel) {
            throw new WarehouseNotFoundException("Warehouse '{$warehouseName}' does not exist");
        }

        $warehouseId = $warehouseModel->getId();

        if (!isset($this->instances[$warehouseId])) {
            $this->instances[$warehouseId] = $this->createWarehouse($warehouseModel);
        }

        return $this->instances[$warehouseId];
    }

    private function createWarehouse(\App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse $warehouseModel): Warehouse
    {
        $packages = $warehouseModel->getPackages()->map(static function (PackageModel $package) {
            return new Package($package->getName(), $package->getCost(), $package->getMaxWeightAllowed());
        })->toArray();

        return new Warehouse(
            $warehouseModel->getId(),
            $warehouseModel->getName(),
            $this->getCostStrategyByType($warehouseModel->getCostStrategyType()),
            $this->carrierBuilder->getByService($warehouseModel->getCarrierService()),
            new PackagingHandler($packages),
            $this->warehouseProductRepository,
            $this->kpyPublicApi,
        );
    }

    /**
     * @throws WarehouseException
     */
    private function getCostStrategyByType(CostStrategyType $type): WarehouseCostStrategyInterface
    {
        /** @var WarehouseCostStrategyInterface $costStrategy */
        foreach ($this->costStrategies as $costStrategy) {
            if ($type === $costStrategy->getType()) {
                return $costStrategy;
            }
        }

        throw new WarehouseException('No cost strategy available for ' . $type->value);
    }

    public function getAll(): array
    {
        $warehousesModels = $this->entityManager->getRepository(\App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse::class)->findAll();

        return array_map(fn($model): Warehouse => $this->createWarehouse($model), $warehousesModels);
    }
}
