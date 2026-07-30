<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class StockPrestashopOnly implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.stock_prestashop_only';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "select p.id_product, ifnull(pa.id_product_attribute, 0) as id_product_attribute
                    from ps_product p
                    left join ps_product_attribute pa
                        on pa.id_product = p.id_product
                    left join ps_stock_available sa
                        on sa.id_product = p.id_product and sa.id_product_attribute = ifnull(pa.id_product_attribute, 0)
                    where sa.out_of_stock != 1
                      and sa.quantity > 0
                      and not exists (select 1 from ps_neftys_stock ns where ns.id_product=sa.id_product and ns.id_product_attribute=sa.id_product_attribute)
                      and not exists (select 1 from ps_kpy_packs kp where kp.id_product_pack = CONCAT_WS('-', p.id_product, ifnull(pa.id_product_attribute, 0)))"
        );
    }
}
