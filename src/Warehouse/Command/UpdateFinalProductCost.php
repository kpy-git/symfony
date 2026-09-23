<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateFinalProductCost implements CommandInterface
{

    public function __construct(private DatabaseInterface $doctrineDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_final_product_cost';
    }

    public function execute(array $params = []): bool
    {
        $sql = "UPDATE warehouse_product
            SET final_cost_price={$params['final_cost']}
            WHERE id_product={$params['id_product']} AND id_product_attribute={$params['id_product_attribute']}";

        if (isset($params['warehouse'])) {
            $sql .= " AND warehouse_id=" . $params['warehouse'];
        }

        return $this->doctrineDatabase->execute($sql);
    }
}
