<?php

namespace App\Warehouse\Query;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class AccumulatedSalesByWarehouseQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.accumulated_sales';
    }

    /**
     * @throws KpyException
     */
    public function fetch(array $params = []): array
    {
        $group = $params['group_by'] ?? '' === 'warehouse' ? ', OP.CENTRO' : '';

        $filter = match ($params['filter'] ?? '') {
            '30d' => ' AND OP.FECHA >= CONVERT(date, DATEADD(m, -1, GETDATE())) AND OP.FECHA < CONVERT(DATE, GETDATE()) ',
            '90d' => ' AND OP.FECHA BETWEEN CONVERT(date, DATEADD(m, -4, GETDATE())) AND CONVERT(date, DATEADD(m, -1, GETDATE())) ',
            default => throw new KpyException('Es necesario usar algún filtro para utilizar esta consulta')
        };

        return $this->aquaDatabase->execute(
            "SELECT RTRIM(P.CODIGO) AS CODIGO, SUM(MO.UNIDADES) AS VENTAS, RTRIM(OP.CENTRO) AS ALMACEN
            FROM DATIN03 P WITH(NOLOCK)
            INNER JOIN DATMO03 MO WITH(NOLOCK) ON MO.CODART=P.CODIGO
            INNER JOIN DATOP03 OP WITH(NOLOCK) ON MO.NUMERO=OP.NUMERO
                {$filter}
                AND (OP.TIPOOPER='C' OR (OP.TIPOOPER='T' AND OP.CENTRO='TIENDA'))
                AND OP.FORMAP NOT IN ('PYM','GRT')
            WHERE P.CONTROLADO=1 AND MO.IMPORTE > 0
            GROUP BY P.CODIGO {$group}
            having SUM(MO.UNIDADES) > 0"
        );
    }
}
