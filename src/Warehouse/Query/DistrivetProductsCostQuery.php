<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class DistrivetProductsCostQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.distrivet_products_cost';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT id_product, id_product_attribute, cost from ps_kpy_distrivet_products_costs"
        );
    }
}
