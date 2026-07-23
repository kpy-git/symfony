<?php

namespace App\Warehouse\Infrastructure\API;


use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;

readonly class WarehousePublicApi implements WarehousePublicApiInterface
{
    public function __construct(private WarehouseProductRepository $warehouseProductRepository)
    {
    }

    public function getDefaultFulfillmentCostsIndexByProduct(): array
    {
        return $this->warehouseProductRepository->findAllDefaultProductFulfillmentCost();
    }
}
