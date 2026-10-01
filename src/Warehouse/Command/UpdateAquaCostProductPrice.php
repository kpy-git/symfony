<?php

namespace App\Warehouse\Command;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Exception\KpyProductNotFoundException;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateAquaCostProductPrice implements CommandInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_aqua_cost_product_price';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['supplier'], $params['sku'], $params['cost'])) {
            throw new KpyException('Supplier, cost and SKU are required.');
        }

        $existsProduct = $this->aquaDatabase->getValue(
            "SELECT IIF(EXISTS(SELECT 1 FROM DATIN03 WITH(NOLOCK) WHERE CODIGO='{$params['sku']}'), 'SI', 'NO') AS 'EXISTS'"
        );

        if ($existsProduct === 'NO') {
            throw new KpyProductNotFoundException('SKU does not exist in AQUA');
        }

        $existsCostPrice = $this->aquaDatabase->getValue(
            "SELECT IIF(EXISTS (SELECT 1 FROM DATPC03 WITH(NOLOCK) WHERE CODIGO='{$params['sku']}' AND PROVEEDOR='{$params['supplier']}'), 'SI', 'NO') AS 'EXISTS'"
        ) === 'SI';

        $discount = $params['discount'] ?? 0.0;

        if ($existsCostPrice) {
            return $this->aquaDatabase->execute(
                "UPDATE DATPC03 SET FOB={$params['cost']}, DESCUENTO={$discount}
               WHERE PROVEEDOR='{$params['supplier']}'
                 AND CODIGO='{$params['sku']}'");
        }

        return $this->aquaDatabase->execute(
            "INSERT INTO DATPC03 (CODIGO, FOB, MONEDA, F_COSTE, PROVEEDOR, DESCUENTO, NOTIMPORT)
        VALUES ('{$params['sku']}', {$params['cost']}, 'EUR', 1, '{$params['supplier']}', {$discount} , 1)");
    }
}
