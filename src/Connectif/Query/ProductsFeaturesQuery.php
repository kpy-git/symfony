<?php

namespace App\Connectif\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductsFeaturesQuery implements ConnectifQueryInterface
{
    public function __construct(#[Autowire(service: 'kompyDatabase')] private DatabaseInterface $kompyDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.connectif.query.products_features';
    }

    public function fetch(array $params = []): array
    {
        return $this->kompyDatabase->execute(
            "SELECT p.id_product, CONCAT_WS(' | ' ,fl.name, fvl.value) as etiqueta
                FROM ps_product p
                INNER JOIN ps_feature_product fp on fp.id_product = p.id_product
                LEFT JOIN ps_feature_lang fl on fl.id_feature = fp.id_feature  AND fl.id_lang = 1
                LEFT JOIN ps_feature_value_lang fvl on fvl.id_feature_value = fp.id_feature_value ANd fvl.id_lang = fl.id_lang
                ORDER BY p.id_product"
        );
    }
}
