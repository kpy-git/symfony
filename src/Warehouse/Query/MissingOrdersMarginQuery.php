<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class MissingOrdersMarginQuery implements QueryInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.missing_orders_margin';
    }

    public function fetch(array $params = []): array
    {
        $sql = "SELECT NUMERO_DOC
            FROM DATOP03 OP WITH(NOLOCK)
            WHERE OP.TIPOOPER='C'
                AND NOT EXISTS (SELECT 1 FROM DATKPYORDERMARGIN03 OM WITH(NOLOCK) WHERE OP.NUMERO_DOC=OM.PEDIDO)";

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $sql = str_replace('SELECT', 'SELECT TOP ' . $params['limit'], $sql);
        }

        $sql .= " ORDER BY NUMERO_DOC";

        return $this->aquaDatabase->execute(
            $sql
        );
    }
}
