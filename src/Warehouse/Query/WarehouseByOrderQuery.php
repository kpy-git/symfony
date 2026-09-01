<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class WarehouseByOrderQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.warehouse_by_order';
    }

    public function fetch(array $params = []): mixed
    {
        return $this->kompyDatabase->getValue(
            "SELECT warehouse
            FROM ps_kpy_order_warehouse
            WHERE id_order = {$params['id_order']}"
        );
    }
}
