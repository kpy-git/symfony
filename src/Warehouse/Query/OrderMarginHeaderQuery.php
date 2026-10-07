<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderMarginHeaderQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_margin_header';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->getRow(
            "select o.id_order, o.module, o.total_paid, o.total_paid_tax_excl, o.total_discounts_tax_excl, ow.warehouse
            from ps_orders o
            inner join ps_kpy_order_warehouse ow on ow.id_order = o.id_order
            where o.id_order = " . $params['id_order']
        );
    }
}
