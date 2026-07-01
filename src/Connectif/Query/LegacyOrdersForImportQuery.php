<?php

namespace App\Connectif\Query;

use App\Connectif\ConnectifException;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LegacyOrdersForImportQuery implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'pymLegacyDatabase')] private DatabaseInterface $pymDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.query.legacy_orders_for_import';
    }

    /**
     * @throws ConnectifException
     */
    public function fetch(array $params = []): \PDOStatement
    {
        $shop = $params['shop'] ?? 1;

        if (!isset($params['first_order'])) {
            throw new ConnectifException('Es necesario el primer pedido para obtener los resultados deseados');
        }

        $sql = "SELECT o.id_order,
                    o.id_customer,
                    o.id_cart,
                    c.email,
                    o.payment,
                    round(o.total_paid,2) as total_paid,
                    o.date_add as fecha
                FROM ps_orders o
                INNER JOIN ps_customer c
                    on c.id_customer = o.id_customer AND c.deleted = 0
                INNER JOIN ps_carrier oc
                    ON oc.id_carrier = o.id_carrier and oc.id_reference != 8
                WHERE o.module != 'free_order'
                    and o.total_paid > 0
                    and o.id_customer > 5
                    and o.id_customer NOT IN (SELECT id_customer FROM ps_pym_marketplaces)
                    and o.id_shop = $shop
                    and o.id_order >= {$params['first_order']}
                ORDER BY o.id_order limit 1000";

        return $this->pymDatabase->prepareForSelect($sql);
    }
}
