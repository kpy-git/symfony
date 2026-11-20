<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class PrebuyOrderProductsQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.prebuy_order_products';
    }

    public function fetch(array $params = []): array
    {
        $sql = "SELECT RTRIM(MO.CODART) AS SKU,
                MO.UNIDADES - MO.INCORPORAD AS UNIDADES,
                CAST(P.DESCRIPTEC AS VARCHAR(255)) AS PRODUCTO,
                RTRIM(F.NOMBRE) AS MARCA,
                ISNULL(ST.STOCK_DISPONIBLE, 0) AS STOCK
            FROM DATMO03 MO WITH(NOLOCK)
            INNER JOIN DATIN03 P WITH(NOLOCK)
                ON P.CODIGO = MO.CODART AND P.CONTROLADO = 1
            LEFT JOIN DATPYMFABRICANTES03 F WITH(NOLOCK)
                ON F.CODIGO = P.FABRICANTE
            LEFT JOIN KPYSTOCKTIENDA ST
                ON ST.SKU = MO.CODART
            WHERE MO.NUMERO = " . $params['operation']
                . " --EVITA SACAR LINEAS CANCELADAS
                AND MO.UNIDADES - MO.INCORPORAD > 0";

        return $this->aquaDatabase->execute($sql);
    }
}
