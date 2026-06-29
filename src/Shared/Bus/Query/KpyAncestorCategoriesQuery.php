<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyAncestorCategoriesQuery implements KpyQueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.shared.ancestor_categories';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "select CONCAT_WS('-', c.id_category, cl.name) as category
                    from ps_category c
                    inner join ps_category_lang cl
                        on cl.id_category = c.id_category and cl.id_shop = 1 and cl.id_lang = 1
                    where c.nleft <= {$params['nleft']} and c.nright >= {$params['nright']} and c.id_category > 807
                    order by c.id_category asc"
        );
    }
}
