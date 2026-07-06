<?php

namespace App\Google\Infrastructure\API;

use App\Google\Domain\Query\QueryBus;
use App\Shared\Domain\ValueObject\ProductCode;

readonly class GooglePublicApi implements GooglePublicApiInterface
{
    public function __construct(
        private QueryBus $queryBus,
    )
    {
    }

    public function getSuggestedInfoByProduct(ProductCode $productCode): array
    {
        return [];
    }

    public function getAllProductSuggestedInfo(string $countryISOCode): array
    {
        $results = $this->queryBus->fetch('kpy.google.query.products_suggested_info');

        if (empty($results)) {
            return [];
        }

        return array_reduce($results, static function (array $carry, array $row) {
            $carry[$row['sku']] = [
                'google_ranking' => $row['ranking'] === null ? 9999 : (int)$row['ranking'],
                'google_suggested_price' => (float)$row['suggested_price'],
                'google_potential_clicks' => (float)$row['potential_click_increase'],
                'google_potential_conversions' => (float)$row['potential_conversion_increase'],
                'google_potential_efficiency' => $row['potential_efficiency'] ?? '-',
            ];
            return $carry;
        }, []);
    }
}
