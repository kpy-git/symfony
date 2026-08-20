<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\Service\OrderStatusUpdater;
use App\Warehouse\Domain\ValueObject\OrderState;
use App\Warehouse\Query\QueryBus;
use App\Warehouse\Service\CarrierStatesAssociator;

readonly class OrderTrackerUpdater
{
    public function __construct(
        private QueryBus                   $queryBus,
        private CarrierTrackeableInterface $carrier,
        private CarrierStatesAssociator    $carrierStatesAssociator,
        private OrderStatusUpdater         $orderStatusUpdater,
    )
    {
    }

    public function updateOrdersHistory(): void
    {
        $orders = $this->queryBus->fetch('kpy.warehouse.query.orders_for_tracking_history', [
            'states' => $this->carrierStatesAssociator->getStatesForSearchingByCarrier('MRW'),
            'warehouse' => 'TIENDA',
        ]);

        /** @var OrderState $order */
        foreach ($orders as $order) {
            $history = $this->carrier->getHistoryByTrackingNumberAfter($order->getTrackingNumber(), $order->getUpdateAt()->getTimestamp());

            foreach ($history as $historyItem) {
                $newState = $this->carrierStatesAssociator->getOwnStateBy($historyItem->state);

                if ($newState === $order->getCurrentState()) {
                    continue;
                }

                $this->orderStatusUpdater->setCurrentState($newState, $historyItem->datetime);

                $order->setCurrentState($newState);
                $order->setUpdateAt($historyItem->datetime);
            }
        }
    }
}
