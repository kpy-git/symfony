<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class ProductsWithFixedPriceQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.products_with_fixed_price';
    }

    public function fetch(array $params = []): array
    {
        return $this->doctrineDatabase->execute(
            "SELECT id_product, id_product_attribute FROM priceshape_product_fixed_price"
        );
    }
}
