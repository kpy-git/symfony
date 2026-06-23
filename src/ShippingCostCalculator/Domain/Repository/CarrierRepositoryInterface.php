<?php

namespace App\ShippingCostCalculator\Domain\Repository;

use App\ShippingCostCalculator\Domain\Carrier;

interface CarrierRepositoryInterface
{
    public function findById(int $id): Carrier;

    public function findByName(string $name): Carrier;

    public function findByService(string $service): Carrier;

    public function add(Carrier $carrierBoundedContext): void;
}
