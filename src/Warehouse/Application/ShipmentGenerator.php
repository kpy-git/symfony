<?php

namespace App\Warehouse\Application;

use App\Warehouse\Domain\Carrier\CarrierFactory;
use App\Warehouse\Domain\Exception\CarrierNotFoundException;
use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;

readonly class ShipmentGenerator
{
    public function __construct(
        private readonly CarrierFactory $carrierFactory
    )
    {
    }

    /**
     * @throws CarrierNotFoundException
     */
    public function generateShipment(Order $order, int $parcels): Shipment
    {
        return $this->carrierFactory->getMRWCordoba()->createShipment($order, $parcels);
    }
}
