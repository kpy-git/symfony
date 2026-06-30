<?php

namespace App\Shared\Bus\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('kpy.shared.query')]
readonly class KpySalesPricesQuery implements KpyQueryInterface
{
    public function __construct(#[Autowire(service: 'aquaDatabase')] private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.query.shared.sales_prices';
    }

    public function fetch(array $params = []): array
    {
        return $this->aquaDatabase->execute(
            "SELECT RTRIM(PRODUCTO) AS SKU, PYM AS 'SALES_PRICE' FROM DATPYMPRDPRICES01 WITH(NOLOCK)"
        );
    }
}
