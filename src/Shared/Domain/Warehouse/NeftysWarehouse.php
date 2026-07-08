<?php

namespace App\Shared\Domain\Warehouse;

use App\Shared\Domain\Warehouse\ValueObject\Product;
use App\ShippingCostCalculator\Domain\Repository\CarrierRepositoryInterface;

class NeftysWarehouse extends Warehouse
{
    public function __construct(CarrierRepositoryInterface $carrierRepository)
    {
        $this->carrier = $carrierRepository->findByService('6');
    }

    public function computeFinalCostPrice(Product $product, int $quantity = 1): float
    {

    }
}
