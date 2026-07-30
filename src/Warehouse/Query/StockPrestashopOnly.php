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
            "select sa.id_product, sa.id_product_attribute
                from ps_stock_available sa
                where sa.out_of_stock != 1
                    and sa.quantity > 0
                    and not exists (select 1 from ps_neftys_stock ns where ns.id_product=sa.id_product and ns.id_product_attribute=sa.id_product_attribute)"
        );
    }
}
