<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class OrderMarginHeaderQuery implements QueryInterface
{

    public function __construct(
        private DatabaseInterface $kompyDatabase,
        #[Autowire('%env(int:CANCELLED_OS)%')]
        private int $cancelledOS
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_margin_header';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->getRow(
            "select o.id_order,
                o.module,
                o.total_paid,
                o.total_paid_tax_excl,
                o.total_discounts_tax_excl,
                ow.warehouse,
                if(exists(select 1 from ps_order_history oh where o.id_order=oh.id_order and oh.id_order_state={$this->cancelledOS}), 1, 0) as `cancelled`
            from ps_orders o
            inner join ps_kpy_order_warehouse ow on ow.id_order = o.id_order
            where o.id_order = " . $params['id_order']
        );
    }
}
