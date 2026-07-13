<?php

namespace App\ShippingCostCalculator\Domain\Builder;

use App\Shared\Domain\Destination;
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

    public function getByService(string $service): Carrier
    {
        $carrier = $this->carrierRepository->findByService($service);

        $carrier->setRangesByDestination(
            Destination::PENINSULA,
            $this->rangeRepository->findAllByServiceAndDestination($carrier->getServiceId(), Destination::PENINSULA));

        return $carrier;
    }

    public function getMRW(): Carrier
    {
        return $this->getByService('6');
    }
}
