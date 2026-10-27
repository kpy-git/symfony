<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class DeprecatedAquaCostPriceQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.deprecated_aqua_cost_price';
    }

    public function fetch(array $params = []): mixed
    {
        $sql = "SELECT ISNULL((PC.FOB * (100-PC.DESCUENTO))/100, P.PRECIOCOMP)
                    * (1 - (ISNULL(PV.DESCUENTO1, 0)/100)) * (1 - (ISNULL(PV.DESCUENTO2, 0)/100)) * (1 - (ISNULL(PV.DESCUENTO3, 0)/100))
                    * (1 - (ISNULL(PV.DESCUENTO4, 0)/100)) * (1 - (ISNULL(PV.DESCUENTO5, 0)/100)) * (1 - (ISNULL(PV.DESCUENTO6, 0)/100))
                    * ISNULL((SELECT (1 - (ISNULL(R.RAPPEL1, 0)/100))*(1 - (ISNULL(R.RAPPEL2, 0)/100))*(1 - (ISNULL(R.RAPPEL3, 0)/100))*
                            (1 - (ISNULL(R.RAPPEL4, 0)/100))*(1 - (ISNULL(R.RAPPEL5, 0)/100)) * (1 - (ISNULL(R.RAPPEL6, 0)/100))
                            FROM DATPYMRAPPEL03 R WITH(NOLOCK)
                            WHERE R.FABRICANTE=P.FABRICANTE AND GETDATE() BETWEEN FECHAINICIAL AND FECHAFINAL+1 AND R.PROVEEDOR=FP.PROVEEDOR), 1)
                    * (CASE WHEN P.TIPOIVA = 3 THEN 1.10 WHEN P.TIPOIVA = 2 THEN 1.21 ELSE 1.04 END)
        FROM DATIN03 P WITH(NOLOCK)
        LEFT JOIN DATPYMFABRICPROVEE03 FP WITH(NOLOCK) ON FP.FABRICANTE=P.FABRICANTE
        LEFT JOIN DATPROV03 PV WITH(NOLOCK) ON PV.NIF=FP.PROVEEDOR
        LEFT JOIN DATPC03 PC WITH(NOLOCK) ON PC.CODIGO=P.CODIGO AND PC.PROVEEDOR=PV.NIF
        WHERE P.CODIGO='{$params['sku']}' AND FP.DEFECTO=1";

        return $this->aquaDatabase->getValue($sql);
    }
}
