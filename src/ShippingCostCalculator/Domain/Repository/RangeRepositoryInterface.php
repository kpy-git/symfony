<?php

namespace App\ShippingCostCalculator\Domain\Repository;

use App\Shared\Domain\Destination;

interface RangeRepositoryInterface
{
    public function findAllByServiceAndDestination(string $service, Destination $destination): array;
}
