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
        if ($this->doctrineDatabase->getValue(
            "select exists (select 1 from warehouse_product
                        where warehouse_id={$params['warehouse']}
                          and id_product={$params['id_product']}
                          and id_product_attribute={$params['id_product_attribute']}) as `exists`") > 0) {

            $update = "UPDATE warehouse_product
            SET final_cost_price={$params['final_cost']}
            WHERE id_product={$params['id_product']} AND id_product_attribute={$params['id_product_attribute']}";

            if (isset($params['warehouse'])) {
                $update .= " AND warehouse_id=" . $params['warehouse'];
            }

            return $this->doctrineDatabase->execute($update);
        }

        $stmt = $this->doctrineDatabase->prepare(
            "INSERT INTO warehouse_product (id_product, id_product_attribute, final_cost_price, is_default, warehouse_id, fulfillment_price)
            VALUES (:id_product, :id_product_attribute, :final_cost_price, :is_default, :warehouse_id, :fulfillment_price)");

        $stmt->execute([
            ':id_product' => $params['id_product'],
            ':id_product_attribute' => $params['id_product_attribute'],
            ':final_cost_price' => $params['final_cost'],
            ':is_default' => 1,
            ':warehouse_id' => $params['warehouse'],
            ':fulfillment_price' => $params['final_cost'],
        ]);

        if (($params['warehouse'] ?? 0) != 1) {
            $stmt->execute([
                ':id_product' => $params['id_product'],
                ':id_product_attribute' => $params['id_product_attribute'],
                ':final_cost_price' => $params['final_cost'],
                ':is_default' => 0,
                ':warehouse_id' => 1,
                ':fulfillment_price' => $params['final_cost'],
            ]);
        }

        return true;

    }
}
