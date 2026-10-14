<?php

namespace App\Warehouse\Query;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class OrderMarginProductsQuery implements QueryInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.query.order_margin_products';
    }

    public function fetch(array $params = []): array
    {
        return $this->aquaDatabase->execute(
            "SELECT MO.NUMERO, MO.POSICION, RTRIM(MO.CODART) AS sku, MO.UNIDADES as quantity
            FROM DATMO03 MO WITH(NOLOCK)
            WHERE MO.NUMERO = (SELECT NUMERO FROM DATOP03 WITH(NOLOCK) WHERE NUMERO_DOC='{$params['id_order']}' AND TIPOOPER='C')
            AND EXISTS (SELECT 1 FROM DATIN03 P WITH(NOLOCK) WHERE P.CODIGO = MO.CODART AND P.CONTROLADO=1)"
        );
    }
}
