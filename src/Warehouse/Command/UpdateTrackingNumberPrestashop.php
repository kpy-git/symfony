<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateTrackingNumberPrestashop implements CommandInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_trackingnumber_prestashop';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['orderId'], $params['trackingNumber'])) {
            throw new \InvalidArgumentException('Order id and tracking number are required.');
        }

        return $this->kompyDatabase->execute(
            "UPDATE ps_order_carrier SET tracking_number = '{$params['trackingNumber']}' WHERE id_order = {$params['orderId']}"
        );
    }
}
