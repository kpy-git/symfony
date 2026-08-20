<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderTrackingHistory implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.orders_for_tracking_history';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "with orders_warehouse as (
                    select id_order from ps_kpy_order_warehouse where warehouse = '{$params['warehouse']}'
                )
                select o.id_order, o.current_state, oh.date_add
                from ps_orders o
                inner join orders_warehouse ow on ow.id_order = o.id_order
                inner join ps_order_history oh
                    on oh.id_order = o.id_order
                where o.current_state in (" . implode(',', $params['states']) . ")
                order by oh.date_add"
        );
    }
}
