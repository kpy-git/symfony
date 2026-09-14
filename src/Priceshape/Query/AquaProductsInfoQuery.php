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
            "SELECT RTRIM(P.CODIGO) AS SKU,
                   RTRIM(R.CODIGO) AS REFERENCIA,
                   P.PESO,
                   PR.GRUPO,
                   RTRIM(P.GRUPOLOGISTICO) AS GRUPOLOGISTICO,
                   PR.BRAND_RANKING,
                   ISNULL(VC.VENTAS_30, 0) AS VENTAS_30,
                   (SELECT TOP 1 RTRIM(E.EAN)
                    FROM DATWMREAN03 E WITH(NOLOCK)
                    WHERE E.PRODUCTO=P.CODIGO AND LEN(EAN) >= 7
                    ORDER BY ALTA DESC) AS EAN
            FROM DATIN03 P WITH(NOLOCK)
            LEFT JOIN DATPYMPRDPRICES03 PR WITH(NOLOCK)
                ON PR.PRODUCTO = P.CODIGO
            LEFT JOIN DATCAPR03 R WITH(NOLOCK)
                ON R.CODART = P.CODIGO
            LEFT JOIN DATKPYVENTASACC03 VC WITH(NOLOCK)
                ON VC.CODIGO = P.CODIGO
            WHERE P.CONTROLADO = 1 AND P.DESCATALOGADO = 0"
        );
    }
}
