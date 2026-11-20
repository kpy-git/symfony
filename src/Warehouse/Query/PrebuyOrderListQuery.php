<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class PrebuyOrderListQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.prebuy_orders_list';
    }

    public function fetch(array $params = []): array
    {
        $sql = "SELECT RTRIM(OPS.PEDIDO) AS PEDIDO, OP.FECHA, OP.HORA, OP.NUMERO
        FROM DATKPYORDERPENDSTOCK03 OPS WITH(NOLOCK)
        INNER JOIN DATOP03 OP WITH(NOLOCK) ON OP.NUMERO_DOC = OPS.PEDIDO
        WHERE OP.PENDIENTES > 0";

        return $this->aquaDatabase->execute($sql);
    }
}
