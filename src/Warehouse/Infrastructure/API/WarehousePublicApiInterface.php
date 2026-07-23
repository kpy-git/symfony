<?php

namespace App\Warehouse\Infrastructure\API;

interface WarehousePublicApiInterface
{
    public function getDefaultFulfillmentCostsIndexByProduct(): array;
}
