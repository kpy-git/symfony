<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductsWithFixedPriceQuery implements QueryInterface
{

    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.products_with_fixed_price';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT id_product, id_product_attribute FROM priceshape_product_fixed_price"
        );
    }
}
