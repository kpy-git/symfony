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
            "WITH PENDIENTES AS (
                    SELECT MO.CODART, SUM(MO.UNIDADES - MO.INCORPORAD) AS UNIDADES
                        FROM DATOP03 OP WITH(NOLOCK)
                        INNER JOIN DATMO03 MO WITH(NOLOCK)
                            ON MO.NUMERO = OP.NUMERO AND MO.UNIDADES > MO.INCORPORAD
                        WHERE OP.TIPOOPER = 'C' AND OP.CENTRO = 'TIENDA' AND OP.PENDIENTES > 0
                        GROUP BY MO.CODART
                )
                SELECT RTRIM(A.CODIGO) AS SKU, A.EXISTENCIA - ISNULL(PENDIENTES.UNIDADES, 0) AS STOCK
                FROM DATAS03 A WITH(NOLOCK)
                LEFT JOIN PENDIENTES ON PENDIENTES.CODART = A.CODIGO
                WHERE A.ALMACEN = 'TIENDA'
                    AND (A.EXISTENCIA - ISNULL(PENDIENTES.UNIDADES, 0)) > 0"
        );
    }
}
