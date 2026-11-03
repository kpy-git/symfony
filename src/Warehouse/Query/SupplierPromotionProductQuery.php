<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class SupplierPromotionProductQuery implements QueryInterface
{

    public function __construct(
        private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.supplier_promotion_product';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->getRow(
            "select value, id_supplier_promo_type as `promo_type`
            from ps_kpy_supplier_promo_products
            where '{$params['date_order']}' between date_from and date_to
              and id_product={$params['id_product']}
              and id_product_attribute={$params['id_product_attribute']}"
        );
    }
}
