<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderMarginProductsQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_margin_products';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "with packs as (
                SELECT pp.id_product_pack, pp.quantity, pp.id_product_item, pp.id_product_attribute_item
                FROM ps_kpy_packs pp
                GROUP BY id_product_pack
                HAVING COUNT(*) = 1
            )
            select if(packs.quantity is not null, CONCAT_WS('-', packs.id_product_item, packs.id_product_attribute_item), CONCAT_WS('-', od.product_id, od.product_attribute_id)) as `sku`,
                   if(packs.quantity is not null, od.product_quantity * packs.quantity, od.product_quantity) as `quantity`,
                   IF(packs.quantity is not null, od.unit_price_tax_excl/(od.product_quantity*packs.quantity), od.unit_price_tax_excl) as `unit_sales_price`
            from ps_order_detail od
            LEFT JOIN packs ON packs.id_product_pack = CONCAT_WS('-', od.product_id, od.product_attribute_id)
            where od.id_order = {$params['id_order']}"
        );
    }
}
