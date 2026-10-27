<?php

namespace App\Warehouse\Service;

use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyProductNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Domain\Exception\WarehouseNotFoundException;
use App\Warehouse\Domain\Warehouse;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use App\Warehouse\Query\QueryBus;

readonly class ProductCostFinder
{
    public function __construct(
        private QueryBus                   $queryBus,
        private WarehouseProductRepository $warehouseProductRepository,
        private WarehouseFactory           $warehouseFactory,
    )
    {
    }

    /**
     * @throws WarehouseNotFoundException
     * @throws KpyProductNotFoundException
     * @throws KpyQueryNotFoundException
     */
    public function getFinalCostPrice(ProductCode $productCode, Warehouse $warehouse): float
    {
        $productWarehouseModel = $this->warehouseProductRepository->findProductInWarehouse($productCode, $warehouse);
        if ($productWarehouseModel !== null) {
            return $productWarehouseModel->getFinalCostPrice();
        }

        // si no existe en el almacén indicado se busca en TIENDA
        $productWarehouseModel = $this->warehouseProductRepository->findProductInWarehouse($productCode, $this->warehouseFactory->createFrom('TIENDA'));
        if ($productWarehouseModel !== null) {
            return $productWarehouseModel->getFinalCostPrice();
        }

        // como última opción se busca el precio de tarifa más descuentos que tiene en AQUA
        $deprecatedCostPrice = $this->queryBus->fetch('kpy.warehouse.query.deprecated_aqua_cost_price', ['sku' => $productCode->getSku()]);

        if (!$deprecatedCostPrice) {
            throw new KpyProductNotFoundException('Product not found: ' . $productCode);
        }

        return $deprecatedCostPrice;


    }
}
