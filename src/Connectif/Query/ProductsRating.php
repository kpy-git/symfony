<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductsRating implements ConnectifQueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.connectif.products_rating';
    }

    public function fetch(array $params = []): mixed
    {
        return $this->kompyDatabase->execute(
            "select id_product, count(*) as `count`, round(avg(grade), 2) as grade_avg
            from ps_product_comment
            group by id_product"
        );
    }
}
