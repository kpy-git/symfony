<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class WarehouseByTrackingNumberQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.warehouse_by_trackingnumber';
    }

    public function fetch(array $params = []): mixed
    {
        return $this->kompyDatabase->getValue(
            "SELECT warehouse
            FROM ps_kpy_order_warehouse
            WHERE id_order = (SELECT id_order FROM ps_order_carrier WHERE tracking_number = '{$params['tracking_number']}')"
        );
    }
}
