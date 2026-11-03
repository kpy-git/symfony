<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class DistrivetBrandsQuery implements QueryInterface
{

    public function __construct(
        private DatabaseInterface $kompyDatabase
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.distrivet_brands';
    }

    public function fetch(array $params = []): array
    {
        return json_decode(
            $this->kompyDatabase->getValue("select value from `ps_configuration` where value = 'KPY_DISTRIVET_MANUFACTURERS'") ?: "[]",
            true,
        );
    }
}
