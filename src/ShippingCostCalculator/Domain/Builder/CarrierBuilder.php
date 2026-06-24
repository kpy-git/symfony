<?php

namespace App\ShippingCostCalculator\Domain\Builder;

use App\Shared\Domain\Aggregate\Destination;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Repository\CarrierRepositoryInterface;
use App\ShippingCostCalculator\Domain\Repository\RangeRepositoryInterface;

readonly class CarrierBuilder
{
    public function __construct(
        private RangeRepositoryInterface $rangeRepository,
        private CarrierRepositoryInterface $carrierRepository,
    )
    {
    }

    public function getMRW(): Carrier
    {
        $carrier = $this->carrierRepository->findByService('6');

        $carrier->setRangesByDestination(
            Destination::PENINSULA,
            $this->rangeRepository->findAllByServiceAndDestination($carrier->getServiceId(), Destination::PENINSULA));

        return $carrier;
    }
}
