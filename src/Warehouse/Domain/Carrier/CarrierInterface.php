<?php

namespace App\Warehouse\Domain\Carrier;

use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;


interface CarrierInterface
{
    public function createShipment(Order $order, int $parcels): Shipment;

    public function getHistoryByTrackingNumberAfter(string $trackingNumber, int $timestamp): array;
}
