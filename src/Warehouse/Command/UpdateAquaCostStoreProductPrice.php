<?php

namespace App\Warehouse\Command;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateAquaCostStoreProductPrice implements CommandInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_cost_price_store_product';
    }

    public function execute(array $params = []): bool
    {
        if (!isset($params['sku'], $params['cost'])) {
            throw new KpyException('Cost and SKU are required.');
        }

        $stmtProduct = $this->aquaDatabase->prepare(
            "UPDATE DATIN03 SET PRECIOENTREEMP=? WHERE CODIGO=?");
        $stmtProduct->execute([
            $params['cost'],
            $params['sku'],
        ]);

        return true;
    }
}
