<?php

namespace App\Connectif;

use App\Connectif\Query\QueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;

readonly class ProductInfoProvider
{
    public function __construct(
        private QueryBus $queryBus,
    ) {}

    /**
     * @throws KpyQueryNotFoundException
     */
    public function findProductsForSync(): array
    {
        return $this->queryBus->fetch('kpy.connectif.query.products');
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function productFeatures(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.connectif.query.products_features'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']][] = $row['etiqueta'];
                return $carry;
            }
        );
    }
}
