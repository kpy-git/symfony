<?php

namespace App\Connectif;

use App\Connectif\Query\ConnectifQueryBus;
use App\Shared\Bus\Query\KpyQueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;

readonly class ProductInfoProvider
{
    public function __construct(
        private ConnectifQueryBus $queryBus,
        private KpyQueryBus       $sharedQueryBus,
    )
    {
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function findProductsForSync(array $filters = []): array
    {
        return $this->queryBus->fetch('kpy.query.connectif.products', $filters);
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function featuresByProductId(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.query.connectif.products_features'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']][] = $row['etiqueta'];
                return $carry;
            }, []
        );
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function relatedProducts(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.query.connectif.related_products'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']][] = $row['related'];
                return $carry;
            }, []
        );
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function productsRatingByProductId(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.query.connectif.products_rating'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']] = [
                    'count' => $row['count'],
                    'rating' => (float)$row['grade_avg'],
                ];
                return $carry;
            }, []
        );
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function firstImageByProductId(): array
    {
        return array_reduce(
            $this->sharedQueryBus->fetch('kpy.query.shared.product_images', ['only_first_image' => true]),
            static function (array $carry, array $row) {
                $carry[$row['id_product']] = $row['id_image'];
                return $carry;
            }, []
        );
    }

    public function estacionalTagsByProductId(): array
    {
        return array_reduce(
            $this->queryBus->fetch('kpy.query.connectif.estacional_tags'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']] = array_map(
                    static fn (string $tag) => 'Estacional | ' . ucfirst($tag),
                    json_decode($row['tag_estacional'], true)
                );

                return $carry;
            }, []
        );
    }

    public function getCombinationImageId(int $id_product_attribute): int
    {
        return $this->sharedQueryBus->fetch('kpy.query.shared.combination_image', [
            'id_product_attribute' => $id_product_attribute,
        ]);
    }

    public function getSalesPricesByProduct(): array
    {
        return array_reduce(
            $this->sharedQueryBus->fetch('kpy.query.shared.sales_prices'),
            static function (array $carry, array $row) {
                $carry[$row['SKU']] = (float)$row['SALES_PRICE'];
                return $carry;
            }, []
        );
    }

    public function getAllProductsSynchronized(): array
    {
        return array_map(
            static fn (array $row) => $row['sku'],
            $this->queryBus->fetch('kpy.connectif.query.products_synchronized')
        );
    }
}
