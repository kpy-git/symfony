<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class ProductsInfoQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.products_info';
    }

    public function fetch(array $params = []): array
    {
        $countryISO = $params['country'] ?? null;

        return $this->doctrineDatabase->execute(
            "SELECT ppi.id_product, ppi.id_product_attribute,
                ppi.matches, ppi.range_position,
                if(exists(select *
                    from priceshape_product_tags ppt
                    where ppt.id_product=ppi.id_product
                        and ppt.id_product_attribute=ppi.id_product_attribute
                        and ppt.country=ppi.country and ppt.tag='Caros'), 'yes', 'no') as caro
            FROM priceshape_product_info ppi
            WHERE ppi.country='{$countryISO}'");
    }
}
