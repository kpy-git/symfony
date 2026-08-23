<?php

namespace App\Warehouse\Query;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UndeliveredOrdersQuery implements QueryInterface
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
        if (!isset($params['states']) || !is_array($params['states'])) {
            throw new KpyException('"states" parameter array expected');
        }

        $limit = (isset($params['limit']) && (int)$params['limit'] > 0) ? " limit " . (int)$params['limit'] : '';

        return $this->kompyDatabase->execute(
            "select o.id_order,
                    o.current_state,
                    (select max(oh.date_add) from ps_order_history oh where oh.id_order = o.id_order and oh.id_order_state = o.current_state) as `date_add`,
                    oc.tracking_number,
                    ow.warehouse,
                    if(exists(select 1 from ps_order_history where id_order_state = 4 and id_order=o.id_order), 1, 0) as `shipped`
                from ps_orders o
                inner join ps_order_carrier oc
                    on oc.id_order = o.id_order
                inner join ps_kpy_order_warehouse ow
                    on o.id_order = ow.id_order
                where o.current_state in (" . implode(',', $params['states']) . ")
                    and oc.tracking_number != ''
                order by `date_add` " . $limit
        );
    }
}
