<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class MainCategoriesQuery implements QueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.main_categories';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT cp.id_product, c.name
                from ps_category_product cp
                inner join (SELECT c2.id_category, cl.name
                    FROM ps_category c1
                    INNER JOIN ps_category c2 ON c2.id_parent=c1.id_category
                    INNER JOIN ps_category_lang cl On cl.id_category=c2.id_category and cl.id_lang=1 AND cl.id_shop=1
                    WHERE c1.id_parent=1308) as c ON c.id_category = cp.id_category"
        );
    }
}
