<?php

namespace App\Google\Domain\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class ProductsSuggestedInfoQuery implements QueryInterface
{
    public function __construct(#[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.google.query.products_suggested_info';
    }

    public function fetch(array $params = []): array
    {
        $countryCode = $params['countryISOCode'] ?? 'ES';

        return $this->doctrineDatabase->execute(
            "select CONCAT_WS('-', id_product, id_product_attribute) as sku, ranking, suggested_price,
                potential_click_increase, potential_conversion_increase, potential_efficiency
            from google_info
            where country = '{$countryCode}'"
        );
    }
}
