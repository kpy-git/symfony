<?php

namespace App\Priceshape\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class AquaProductsInfoQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.priceshape.query.aqua_products_info';
    }

    public function fetch(array $params = []): array
    {
        return $this->aquaDatabase->execute(
            "SELECT RTRIM(P.CODIGO) AS SKU, RTRIM(R.CODIGO) AS REFERENCIA, P.PESO, PR.GRUPO,
                ISNULL(PS.STOCK_DISPONIBLE, 0) AS STOCK, RTRIM(P.GRUPOLOGISTICO) AS GRUPOLOGISTICO, PR.BRAND_RANKING,
                (SELECT TOP 1 RTRIM(E.EAN)
                    FROM DATWMREAN01 E WITH(NOLOCK)
                    WHERE E.PRODUCTO=PR.PRODUCTO AND LEN(EAN) >= 7
                    ORDER BY ALTA DESC) AS EAN,
                ISNULL(VD.VENTAS_30, 0) AS VENTAS_30
            FROM DATPYMPRDPRICES01 PR WITH(NOLOCK)
            INNER JOIN DATIN01 P WITH(NOLOCK)
                 ON P.CODIGO = PR.PRODUCTO AND P.DESCATALOGADO = 0
            LEFT JOIN DATCAPR01 R WITH(NOLOCK)
                ON R.CODART = P.CODIGO
            LEFT JOIN PRODUCTSTOCK PS
	            ON PS.CODIGO = P.CODIGO
            LEFT JOIN DATPYMVENTASACCVD01 VD WITH(NOLOCK)
	            ON VD.CODIGO = P.CODIGO AND VD.VENDEDOR='1'
            WHERE P.CONTROLADO = 1"
        );
    }
}
