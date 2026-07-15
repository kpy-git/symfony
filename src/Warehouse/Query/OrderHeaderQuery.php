<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderHeaderQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_header';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->getRow(
            "select o.id_order,
                   o.date_add,
                   c.email,
                   CONCAT_WS(' ', a.firstname, a.lastname) as `name`,
                   CONCAT_WS(' ', a.address1, a.address2) as `address`,
                   a.postcode,
                   a .city,
                   a.phone,
                   cl.name as `country`, s.name as `state`,
                   if(o.module = 'kpycashondelivery', o.total_paid, 0) as `crm`
            from ps_orders o
            inner join ps_customer c
                on c.id_customer = o.id_customer
            inner join ps_address a
                on a.id_address = o.id_address_delivery
            inner join ps_country_lang cl
                on cl.id_country = a.id_country and cl.id_lang = 1
            inner join ps_state s
                on s.id_state = a.id_state
            where o.id_order = " . $params['id_order']
        );
    }
}
