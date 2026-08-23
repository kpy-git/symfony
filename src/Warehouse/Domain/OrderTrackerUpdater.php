<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\OrderStatusUpdater;
use App\Warehouse\Domain\Carrier\CarrierFactory;
use App\Warehouse\Domain\Carrier\MRW\SeguimientoDTO;
use App\Warehouse\Domain\Exception\TrackingException;
use App\Warehouse\Domain\ValueObject\OrderState;
use App\Warehouse\Domain\ValueObject\TrackingState;
use App\Warehouse\Domain\ValueObject\TrackingUpdateResult;
use App\Warehouse\Query\QueryBus;
use App\Warehouse\Service\CarrierStatesAssociator;
use Psr\Log\LoggerInterface;

readonly class OrderTrackerUpdater
{
    public function __construct(
        private QueryBus                $queryBus,
        private CarrierStatesAssociator $carrierStatesAssociator,
        private OrderStatusUpdater      $orderStatusUpdater,
        private CarrierFactory          $carrierFactory,
        private LoggerInterface         $logger,
        private int                     $shippedOS,
    )
    {
    }

    public function updateHistoryUndeliveredOrders(int $limit = 0): array
    {
        $orders = array_map(
            static function (array $row): OrderState {
                return new OrderState(
                    $row['id_order'],
                    $row['tracking_number'],
                    $row['current_state'],
                    \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_add']),
                    $row['shipped'],
                    $row['warehouse']
                );
            },
            $this->queryBus->fetch('kpy.warehouse.query.orders_for_tracking_history', [
                'states' => $this->carrierStatesAssociator->getStatesForSearchingByCarrier('MRW'),
                'limit' => $limit,
            ])
        );

        $ordersChangedCount = 0;
        $results = [];

        /** @var OrderState $order */
        foreach ($orders as $order) {
            try {
                $carrier = $this->carrierFactory->getByWarehouse($order->getWarehouse());

                $history = $carrier->getHistoryByTrackingNumberAfter($order->getTrackingNumber(), $order->getUpdateAt()->getTimestamp());

                $result = $this->processOrderHistory($order, $history);

                $results[] = $result;

                if ($result->withStatusChanges()) {
                    $ordersChangedCount++;
                }

            } catch (KpyException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return [
            'undelivered_orders' => count($orders),
            'updated_orders' => $ordersChangedCount,
            'results' => $results,
        ];
    }

    private function processOrderHistory(OrderState $order, array $history): TrackingUpdateResult
    {
        $result = new TrackingUpdateResult($order->getOrderId(), $order->getCurrentState(), $order->getUpdateAt());

        /** @var SeguimientoDTO $state */
        foreach ($history as $state) {
            $newState = $this->carrierStatesAssociator->getOwnStateBy('MRW', $state->Estado);

            if ($newState === $order->getCurrentState()) {
                continue;
            }

            // hace que solo pueda tener un 'Enviado' en su historia
            if ($newState === $this->shippedOS && $order->isShipped()) {
                continue;
            }

            $this->orderStatusUpdater->setCurrentState($order->getOrderId(), $newState, 0, $state->Publicado);

            $order->setCurrentState($newState);
            if ($newState === $this->shippedOS) {
                $order->shipped();
            }

            $result->addState(new TrackingState($newState, $state->Publicado, $state->EstadoDescripcion));
            $this->logger->info(sprintf("Pedido %d -> estado %d [%s] (%s)", $order->getOrderId(), $newState, $state->Publicado->format('d/m/Y H:i:s'), $state->EstadoDescripcion));
        }

        return $result;
    }

    public function updateHistoryByOrder(int $orderId): TrackingUpdateResult
    {
        $data = $this->queryBus->fetch('kpy.warehouse.query.order_tracking_info', ['order' => $orderId]);

        if (empty($data)) {
            throw new TrackingException('El pedido no existe o ya no puede ser actualizado');
        }

        $order = new OrderState(
            $data['id_order'],
            $data['tracking_number'],
            $data['current_state'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['date_add']),
            $data['shipped'],
            $data['warehouse']
        );

        $carrier = $this->carrierFactory->getByWarehouse($order->getWarehouse());

        return $this->processOrderHistory(
            $order,
            $carrier->getHistoryByTrackingNumberAfter(
                $order->getTrackingNumber(),
                $order->getUpdateAt()->getTimestamp())
        );
    }

    public function getHistoryByTrackingNumber(string $trackingNumber): array
    {
        $warehouse = $this->queryBus->fetch('kpy.warehouse.query.warehouse_by_trackingnumber',
            ['tracking_number' => $trackingNumber]);

        if (empty($warehouse)) {
            throw new TrackingException('No se ha encontrado ningún almacén que gestione el seguimiento.');
        }

        return $this->carrierFactory
            ->getByWarehouse($warehouse)
            ->getHistoryByTrackingNumberAfter($trackingNumber);
    }
}
