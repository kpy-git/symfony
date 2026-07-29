<?php

namespace App\Warehouse\Domain;

use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.carrier')]
interface ExpeditionableInterface
{
    public function createShipment(Order $order, int $parcels): Shipment;

    public function associatedService(): string;
}
