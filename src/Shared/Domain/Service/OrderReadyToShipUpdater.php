<?php

namespace App\Shared\Domain\Service;

use App\Shared\Bus\Command\KpyCommandBus;

readonly class OrderReadyToShipUpdater
{
    public function __construct(
        private OrderStatusUpdater $orderStatusUpdater,
        private KpyCommandBus $commandBus
    )
    {
    }

    public function updateOrder(int $orderId, string $trackingNumber): void
    {
        $this->commandBus->execute('kpy.shared.command.update_trackingnumber_prestashop', [
            'orderId' => $orderId,
            'trackingNumber' => $trackingNumber(),
        ]);

        $this->orderStatusUpdater->setCurrentState($orderId, (int)$_ENV['SHIP_READY_OS']);
    }
}
