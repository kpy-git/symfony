<?php

namespace App\Warehouse\Query;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderTrackingInfoQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_tracking_info';
    }

    public function fetch(array $params = []): array
    {
        if (!isset($params['order'])) {
            throw new KpyException('"order" is required');
        }

        return $this->kompyDatabase->getRow(
            "select o.id_order,
               o.current_state,
               oc.tracking_number,
               (select max(oh.date_add) from ps_order_history oh where oh.id_order = o.id_order and oh.id_order_state = o.current_state) as `date_add`,
               ow.warehouse,
               if(exists(select 1 from ps_order_history where id_order_state = 4 and id_order=o.id_order), 1, 0) as `shipped`
        from ps_orders o
        inner join ps_order_carrier oc
            on oc.id_order = o.id_order
        inner join ps_kpy_order_warehouse ow
            on ow.id_order = o.id_order
        where o.id_order = " . $params['order']
        );
    }
}
