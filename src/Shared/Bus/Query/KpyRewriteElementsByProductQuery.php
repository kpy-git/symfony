<?php

namespace App\Shared\Bus\Query;

use App\Shared\Domain\Service\Policy\Context\InsufficientParamsForQueryContext;
use App\Shared\Domain\Service\Policy\InsufficientParamsForQueryPolicy;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyRewriteElementsByProductQuery implements KpyQueryInterface
{
    public function __construct(
        private DatabaseInterface $kompyDatabase,
        private InsufficientParamsForQueryPolicy $policy
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.shared.query.rewrite_elements_by_product';
    }

    public function fetch(array $params = []): array
    {
        $this->policy->apply(new InsufficientParamsForQueryContext(
            ['id_product', 'id_lang', 'id_shop'],
            array_keys($params),
        ));

        return $this->kompyDatabase->getRow(
            "select
            pl.link_rewrite as product_rewrite,
            cl.link_rewrite as category_rewrite
        from ps_product_lang pl
        inner join ps_product p
            on p.id_product = pl.id_product
        inner join ps_category_lang cl
            on cl.id_category = p.id_category_default
            and cl.id_shop = pl.id_shop and cl.id_lang = pl.id_lang
        where pl.id_product = {$params['id_product']}
          and pl.id_shop = {$params['id_shop']}
          and pl.id_lang = {$params['id_lang']}"
        );
    }
}
