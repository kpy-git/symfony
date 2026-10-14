<?php

namespace App\Warehouse\Infrastructure\Persistence\Repository;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpySqlException;
use App\Warehouse\Domain\ValueObject\OrderMargin;
use App\Warehouse\Domain\ValueObject\OrderMarginProduct;

readonly class OrderMarginRepository
{
    private const string TABLE_NAME = 'DATKPYORDERMARGIN03';

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    /**
     * @throws KpySqlException
     */
    public function save(OrderMargin $orderMargin): void
    {
        try {
            $this->aquaDatabase->beginTransaction();

            $delete = $this->aquaDatabase->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE PEDIDO=?");
            $delete->execute([$orderMargin->getOrderId()]);

            $stmt = $this->aquaDatabase->prepare(
                "INSERT INTO " . self::TABLE_NAME . " (NUMERO, POSICION, PEDIDO, CODIGO, COSTE_FINAL, SHIPPING_COST,
            PACKING_COST, MANIP_COST, PAY_COMMISION) VALUES (:operation, :position, :order, :sku, :costFinal, :shippingCost,
            :packingCost, :manipulationCost, :paymentCommission)"
            );

            /** @var OrderMarginProduct $product */
            foreach ($orderMargin->getProducts() as $product) {
                $stmt->bindValue(':operation', $product->getOperationNumber(), \PDO::PARAM_INT);
                $stmt->bindValue(':position', $product->getPositionNumber(), \PDO::PARAM_INT);
                $stmt->bindValue(':order', $orderMargin->getOrderId());
                $stmt->bindValue(':sku', $product->getProductCode()->getSku());
                $stmt->bindValue(':costFinal', $product->getCostPrice());
                $stmt->bindValue(':shippingCost', $product->getShippingCost());
                $stmt->bindValue(':packingCost', $product->getPackingCost());
                $stmt->bindValue(':manipulationCost', $product->getManipulationCost());
                $stmt->bindValue(':paymentCommission', $product->getPaymentCommission());

                $stmt->execute();
            }

            $this->aquaDatabase->commit();

        } catch (\PDOException $exception) {
            $this->aquaDatabase->rollBack();

            throw new KpySqlException(
                $exception->getMessage(),
                __METHOD__,
                $this->aquaDatabase->getLastSql(),
                $this->aquaDatabase->getSqlError()
            );
        }
    }
}
