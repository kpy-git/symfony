<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyProductCategoriesQuery implements KpyQueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.shared.query.product_categories';
    }

    public function fetch(array $params = []): array
    {
        $sql = "select id_product,
                    GROUP_CONCAT(CONCAT_WS(':', cp.id_category, c.nleft, c.nright) separator ',') as categories
                from ps_category_product cp
                inner join ps_category c
                    on c.id_category = cp.id_category and c.active = 1";

        if (isset($params['product'])) {
            $sql .= " and cp.id_product = " . $params['product'] ;
        }

        $sql .= " group by id_product";

        return $this->kompyDatabase->execute($sql);
    }
}
