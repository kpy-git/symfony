<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class StockDistrivetQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.stock_distrivet';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT CONCAT_WS('-', id_product, id_product_attribute) as sku, stock from ps_kpy_distrivet_stock"
        );
    }
}
