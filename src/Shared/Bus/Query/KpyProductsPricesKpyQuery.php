<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpyProductsPricesKpyQuery implements KpyQueryInterface
{
    public function __construct(
        #[Autowire(service: 'doctrineDatabase')] private DatabaseInterface $doctrineDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.products_prices';
    }

    public function fetch(array $params = []): array
    {
        return $this->doctrineDatabase->execute(
            "SELECT id_product, id_product_attribute, sales_price_es, final_cost_price FROM kpy_product_prices"
        );
    }
}
