<?php

namespace App\Google\Domain\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class InfoAquaQuery implements QueryInterface
{
    public function __construct(
        #[Autowire(service: 'aquaDatabase')] private DatabaseInterface $aquaDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.query.google.info.aqua';
    }

    public function fetch(array $params = []): array
    {
        $sql = "SELECT P.CODIGO AS PRODUCTO,
                       P.PESO,
                       P.GRUPOLOGISTICO,
                       P.STOCK_SYNC,
                       R.CODIGO AS REFERENCIA,
                       F.NOMBRE AS FABRICANTE,
                       ISNULL(DESCRIPTEC, '') AS DESCRIPTEC,
                       ISNULL(PR.PYM, 0) AS PYM,
                       ISNULL(PR.WEC, 0) AS WEC,
                       ISNULL(PR.ITA, 0) AS ITA,
                       CASE WHEN PR.VALOR_MEDIO = 0 THEN PR.COMPRA_CON_DTOS ELSE PR.VALOR_MEDIO END AS COSTE,
                       ISNULL(PR.COSTE_CAJA, 0) AS COSTE_CAJA,
                       ISNULL(PR.COSTE_ENVIO_ES, 0) AS COSTE_ENVIO_ES,
                       ISNULL(PR.COSTE_ENVIO_PT, 0) AS COSTE_ENVIO_PT,
                       ISNULL(PR.COSTE_ENVIO_IT, 0) AS COSTE_ENVIO_IT,
                       ISNULL(PR.LIQUIDACION, 0) AS LIQUIDACION,
                       CASE WHEN P.TIPOIVA = 3 THEN 1.1 WHEN P.TIPOIVA = 2 THEN 1.21 ELSE 0 END AS IVA_DE_COMPRAS,
                       (SELECT TOP 1 E.EAN FROM DATWMREAN03 E WITH(NOLOCK) WHERE E.PRODUCTO=P.CODIGO ORDER BY E.ALTA DESC) AS EAN
                FROM DATIN03 P WITH(NOLOCK)
                LEFT JOIN DATPYMPRDPRICES03 PR WITH(NOLOCK)
                    ON PR.PRODUCTO = P.CODIGO
                LEFT JOIN DATCAPR03 R WITH(NOLOCK)
                    ON R.CODART = P.CODIGO
                LEFT JOIN DATKPYVENTASACC03 VC WITH(NOLOCK)
                    ON VC.CODIGO = P.CODIGO
                LEFT JOIN DATPYMFABRICANTES01 F WITH(NOLOCK)
                    ON F.CODIGO=P.FABRICANTE";

        return $this->aquaDatabase->execute($sql);
    }
}
