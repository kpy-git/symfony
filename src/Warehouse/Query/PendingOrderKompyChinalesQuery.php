<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class PendingOrderKompyChinalesQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.pending_orders_kompychinales';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "select id_order, date_add
            from ps_orders
            where current_state = {$params['state']}
            order by date_add desc"
        );
    }
}
