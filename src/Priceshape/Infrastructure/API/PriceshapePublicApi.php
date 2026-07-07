<?php

namespace App\Priceshape\Infrastructure\API;

use App\Priceshape\Query\QueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;

readonly class PriceshapePublicApi implements PriceshapePublicApiInterface
{
    public function __construct(private QueryBus $queryBus)
    {
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function getProductsInfoByCountry(string $countryISO): array
    {
        return array_reduce($this->queryBus->fetch('kpy.priceshape.query.products_info', ['country' => $countryISO]),
            static function (array $carry, array $row) {
            $carry[ProductCode::from($row['id_product'], $row['id_product_attribute'])->getSku()] = [
                'matches' => $row['matches'],
                'position' => $row['range_position'],
                'caro' => $row['caro'],
            ];
            return $carry;
        }, []);
    }
}
