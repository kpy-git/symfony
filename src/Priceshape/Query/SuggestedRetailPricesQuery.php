<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class SuggestedRetailPricesQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.suggested_retail_prices';
    }

    public function fetch(array $params = []): array
    {
        $country = $params['country'] ?? 'es';

        return $this->doctrineDatabase->execute(
            "SELECT id_product, id_product_attribute, pvpr
            FROM priceshape_product_pvpr
            WHERE country='" . strtoupper($country) . "'"
        );
    }
}
