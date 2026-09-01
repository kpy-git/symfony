<?php

namespace App\Warehouse\Application;

use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Warehouse\Domain\Carrier\CarrierFactory;
use App\Warehouse\Domain\Exception\CarrierNotFoundException;
use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\Shipment;
use App\Warehouse\Query\QueryBus;

readonly class ShipmentGenerator
{
    public function __construct(
        private CarrierFactory $carrierFactory,
        private QueryBus $queryBus,
    )
    {
    }

    /**
     * @throws CarrierNotFoundException
     * @throws KpyQueryNotFoundException
     */
    public function generateShipment(Order $order, int $parcels): Shipment
    {
        $warehouse = $this->queryBus->fetch('kpy.warehouse.query.warehouse_by_order', [
            'id_order' => $order->getOrderId(),
        ]);

        return $this->carrierFactory->getByWarehouse($warehouse)->createShipment($order, $parcels);
    }
}
