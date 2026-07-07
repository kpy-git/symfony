<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class BrandsBannedQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.brands_banned';
    }

    public function fetch(array $params = []): array
    {
        return $this->doctrineDatabase->execute("SELECT id_manufacturer FROM priceshape_brand_banned");
    }
}
