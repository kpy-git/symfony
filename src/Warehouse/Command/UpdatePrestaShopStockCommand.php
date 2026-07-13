<?php

namespace App\Warehouse\Command;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;

class UpdatePrestaShopStockCommand implements CommandInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_prestashop_stock';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['product_code']) || !$params['product_code'] instanceof ProductCode) {
            return false;
        }

        $productCode = $params['product_code'];
        $quantity = max(0, $params['quantity']);

        if (!$productCode->isCombinationProduct()) {
            return $this->kompyDatabase->execute(
                "UPDATE ps_stock_available
                SET quantity=$quantity
                WHERE id_product={$productCode->getProductId()} and id_product_attribute=0");
        }

        $this->kompyDatabase->execute("UPDATE ps_stock_available
                SET quantity=$quantity
                WHERE id_product={$productCode->getProductId()} and id_product_attribute={$productCode->getProductAttributeId()}");

        return $this->kompyDatabase->execute(
            "UPDATE ps_stock_available sa
                JOIN (
                    SELECT id_product, SUM(quantity) AS total_quantity
                    FROM ps_stock_available
                    WHERE id_product = {$productCode->getProductId()} AND id_product_attribute > 0
                    GROUP BY id_product
                ) sub ON sa.id_product = sub.id_product
                SET sa.quantity = sub.total_quantity
                WHERE sa.id_product = {$productCode->getProductId()} AND sa.id_product_attribute = 0;"
        );
    }
}
