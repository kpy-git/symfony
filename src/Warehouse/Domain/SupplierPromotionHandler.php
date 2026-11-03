<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\ValueObject\Product;
use App\Warehouse\Query\QueryBus;

readonly class SupplierPromotionHandler
{
    public function __construct(private QueryBus $queryBus)
    {
    }

    public function applyPromotionIfExists(Product $product, float $costPrice, string $date_add): float
    {
        $results = $this->queryBus->fetch('kpy.warehouse.query.supplier_promotion_product', [
            'id_product' => $product->getProductCode()->getProductId(),
            'id_product_attribute' => $product->getProductCode()->getProductAttributeId(),
            'date_order' => $date_add,
        ]);

        if (empty($results)) {
            return $costPrice;
        }

        $promotion = SupplierPromotionType::tryFrom($results['promo_type']);

        if (!$promotion) {
            return $costPrice;
        }

        return $promotion->applyPromotion($costPrice, $results['value']);
    }
}
