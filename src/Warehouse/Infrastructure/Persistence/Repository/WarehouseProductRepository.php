<?php

namespace App\Warehouse\Infrastructure\Persistence\Repository;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Shared\Infrastructure\Database\Exception\KpySqlException;
use App\Warehouse\Domain\ValueObject\WarehouseProductFulfillmentCost;
use App\Warehouse\Domain\Warehouse;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\WarehouseProduct;
use Doctrine\ORM\EntityManagerInterface;

class WarehouseProductRepository
{
    private \App\Warehouse\Infrastructure\Persistence\Doctrine\Repository\WarehouseProductRepository $doctrineWarehouseProductRepository;
    public function __construct(
        private readonly DatabaseInterface $doctrineDatabase,
        EntityManagerInterface $entityManager
    )
    {
        $this->doctrineWarehouseProductRepository = $entityManager->getRepository(WarehouseProduct::class);
    }

    public function findProductInWarehouse(ProductCode $productCode, Warehouse $warehouse): ?WarehouseProduct
    {
        return $this->doctrineWarehouseProductRepository->findOneBy([
            'warehouse' => $warehouse->getId(),
            'productId' => $productCode->getProductId(),
            'productAttributeId' => $productCode->getProductAttributeId(),
        ]);
    }

    /**
     * @return WarehouseProduct[] Returns an array of ProductWarehouse objects
     */
    public function findProductsInWarehouse(Warehouse $warehouse): array
    {
        return $this->doctrineWarehouseProductRepository->findBy([
            'warehouse' => $warehouse->getId(),
        ]);
    }

    /**
     * @throws KpySqlException
     */
    public function updateProductsFulfillmentCostBatch(array $warehouseProductsFulfillmentCost): void
    {
        try {
            $this->doctrineDatabase->beginTransaction();

            $stmt = $this->doctrineDatabase->prepare(
                "UPDATE warehouse_product
                        SET fulfillment_price = :fulfillment_cost
                        WHERE id_product = :product_id
                            AND id_product_attribute = :product_attribute_id
                            AND warehouse_id = :warehouse_id"
            );

            /** @var WarehouseProductFulfillmentCost $warehouseProductFulfillmentCost */
            foreach ($warehouseProductsFulfillmentCost as $warehouseProductFulfillmentCost) {
                $stmt->bindValue(':fulfillment_cost', $warehouseProductFulfillmentCost->getFulfillmentCost());
                $stmt->bindValue(':product_id', $warehouseProductFulfillmentCost->getProductCode()->getProductId());
                $stmt->bindValue(':product_attribute_id', $warehouseProductFulfillmentCost->getProductCode()->getProductAttributeId());
                $stmt->bindValue(':warehouse_id', $warehouseProductFulfillmentCost->getWarehouseId());

                $stmt->execute();
            }

            $this->doctrineDatabase->commit();

        } catch (\PDOException $exception) {
            $this->doctrineDatabase->rollBack();
            throw new KpySqlException(
                $exception->getMessage(),
                __METHOD__,
                $this->doctrineDatabase->getLastSql(),
                $this->doctrineDatabase->getSqlError()
            );
        }
    }
}
