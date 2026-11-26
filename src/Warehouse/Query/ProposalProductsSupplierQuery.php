<?php

namespace App\Warehouse\Query;

use App\Shared\Domain\Service\Policy\Context\InsufficientParamsForQueryContext;
use App\Shared\Domain\Service\Policy\InsufficientParamsForQueryPolicy;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class ProposalProductsSupplierQuery implements QueryInterface
{

    public function __construct(
        private DatabaseInterface                $aquaDatabase,
        private InsufficientParamsForQueryPolicy $policy
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.proposal_products_supplier';
    }

    public function fetch(array $params = []): array
    {
        $this->policy->apply(new InsufficientParamsForQueryContext(['supplier'], array_keys($params)));

        $sql = "SELECT PB.CODIGO,
               PB.UNIDADES AS REQUERIDAS,
               IIF(PC.FOB IS NOT NULL, PC.FOB * (1 - (PC.DESCUENTO / 100)), P.PRECIOCOMP) AS PRICE,
               CAST(P.DESCRIPTEC AS VARCHAR(255)) AS PRODUCTO,
               P.UDS_MIN_COMPRA,
               ST.STOCK_DISPONIBLE,
               P.TIPOIVA,
               REF.CODIGO AS REF,
               P.PESO,
               P.VOLUMEN,
               ISNULL(PC.DESCUENTO, 0) AS DESCUENTO,
               ISNULL(PC.FOB, P.PRECIOCOMP) AS TARIFA,
               (SELECT TOP 1 EAN FROM DATWMREAN03 WITH(NOLOCK) WHERE PRODUCTO=P.CODIGO ORDER BY ALTA DESC) AS EAN
        FROM DATKPYPREBUY03 PB WITH(NOLOCK)
        INNER JOIN DATIN03 P WITH(NOLOCK)
            ON P.CODIGO = PB.CODIGO
        INNER JOIN DATPYMFABRICPROVEE03 FP WITH(NOLOCK)
            ON P.FABRICANTE = FP.FABRICANTE AND FP.PROVEEDOR = '{$params['supplier']}'
        INNER JOIN DATPROV03 PROV WITH(NOLOCK)
            ON PROV.NIF = FP.PROVEEDOR
        LEFT JOIN DATPC03 PC WITH(NOLOCK)
            ON PC.CODIGO=P.CODIGO AND PC.PROVEEDOR=FP.PROVEEDOR
        INNER JOIN KPYSTOCKTIENDA ST
            ON ST.SKU = PB.CODIGO
        LEFT JOIN DATCAPR03 REF WITH(NOLOCK)
            ON REF.CODART=P.CODIGO";

        return $this->aquaDatabase->execute($sql);
    }
}
