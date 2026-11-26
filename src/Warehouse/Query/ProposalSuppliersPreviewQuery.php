<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class ProposalSuppliersPreviewQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.proposal_suppliers_preview';
    }

    public function fetch(array $params = []): array
    {
        $sql = "WITH SUPPLIERS AS (
                SELECT PROV.NIF,
                       ROUND(PB.UNIDADES * IIF(PC.FOB IS NOT NULL, PC.FOB * (1 - (PC.DESCUENTO / 100)), P.PRECIOCOMP), 2) AS PRICE
                FROM DATKPYPREBUY03 PB WITH(NOLOCK)
                INNER JOIN DATIN03 P WITH(NOLOCK)
                    ON P.CODIGO = PB.CODIGO
                INNER JOIN DATPYMFABRICPROVEE03 FP WITH(NOLOCK)
                    ON P.FABRICANTE = FP.FABRICANTE AND FP.DEFECTO = 1
                INNER JOIN DATPROV03 PROV WITH(NOLOCK)
                    ON PROV.NIF = FP.PROVEEDOR
                LEFT JOIN DATPC03 PC WITH(NOLOCK)
                    ON PC.CODIGO=P.CODIGO AND PC.PROVEEDOR=FP.PROVEEDOR
            )
            SELECT PROV.COMERCIAL AS NOMBRE,
                PROV.PED_MIN_EURO,
                SUM(PRICE) AS TOTAL, PROV.NIF,
                COUNT(*) AS LINEAS,
                PROV.HORA_CORTE
            FROM DATPROV03 PROV WITH(NOLOCK)
            INNER JOIN SUPPLIERS
                ON SUPPLIERS.NIF = PROV.NIF
            GROUP BY PROV.COMERCIAL, PROV.PED_MIN_EURO, PROV.NIF, PROV.HORA_CORTE
            ORDER BY SUM(PRICE) DESC";

        return $this->aquaDatabase->execute($sql);
    }
}
