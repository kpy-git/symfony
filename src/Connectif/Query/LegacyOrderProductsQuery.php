<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class LegacyOrderProductsQuery implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'pymLegacyDatabase')] private DatabaseInterface $pymDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.query.legacy_order_products';
    }

    public function fetch(array $params = []): array
    {
        if (!isset($params['id_order'])) {
            return [];
        }

        return $this->pymDatabase->execute(
            "select CONCAT_WS('-', od.product_id, od.product_attribute_id) as SKU,
                od.product_quantity,
                round(od.unit_price_tax_incl,2) as unit_price,
                od.product_name,
                round(od.total_price_tax_incl, 2) as total_price,
                pl.link_rewrite as product_link,
                cl.link_rewrite as category_link,
                od.product_attribute_id,
                m.name as `brand`
            from ps_order_detail od
            inner join ps_orders o
                on o.id_order = od.id_order
            inner join ps_product p
                on p.id_product = od.product_id
            inner join ps_product_lang pl
                on pl.id_product = od.product_id and pl.id_shop = o.id_shop and pl.id_lang = 1
            inner join ps_category_lang cl
                on cl.id_category = p.id_category_default and cl.id_shop = pl.id_shop
                and cl.id_lang = pl.id_lang
            inner join ps_manufacturer m
                on m.id_manufacturer = p.id_manufacturer
            where od.id_order = {$params['id_order']}
              and od.product_id not in (4606, 4607)
              and p.visibility = 'both'"
        );
    }
}
