<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class StockKompyChinalesQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.stock_kompy_chinales';
    }

    public function fetch(array $params = []): array
    {
        return $this->aquaDatabase->execute(
            "SELECT RTRIM(A.CODIGO) AS SKU, A.EXISTENCIA AS STOCK
                FROM DATAS03 A WITH(NOLOCK)
                WHERE ALMACEN='TIENDA' AND A.EXISTENCIA > 0"
        );
    }
}
