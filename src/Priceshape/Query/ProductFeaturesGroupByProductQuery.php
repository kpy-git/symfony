<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductFeaturesGroupByProductQuery implements QueryInterface
{
    public function __construct(
        #[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.product_features_group_by_product';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT p.id_product, fl.name as 'feature', GROUP_CONCAT(fvl.value SEPARATOR ' | ') as value
            FROM ps_product p
            INNER JOIN ps_feature_product fp on fp.id_product = p.id_product AND fp.id_feature != 14
            LEFT JOIN ps_feature_lang fl on fl.id_feature = fp.id_feature  AND fl.id_lang = 1
            LEFT JOIN ps_feature_value_lang fvl on fvl.id_feature_value = fp.id_feature_value ANd fvl.id_lang = 1
            GROUP BY p.id_product, fl.name
            ORDER BY p.id_product"
        );
    }
}
