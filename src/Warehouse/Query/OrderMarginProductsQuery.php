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
            AND EXISTS (SELECT 1 FROM DATIN03 P WITH(NOLOCK) WHERE P.CODIGO = MO.CODART AND P.CONTROLADO=1)
            -- EVITA LOS FLYERS
            AND MO.CODART NOT IN ('8508-92926', '8509-92927', '8511-92929', '8517-92937', '8512-92930',
                '8510-92928', '8513-92931', '8514-92932', '8515-92933', '8856-93388', '8857-93389', '9027-93682',
                '9209-94018', '9714-94773', '9713-94772')"
        );
    }
}
