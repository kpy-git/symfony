<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\Destination;
use App\Shared\Domain\ValueObject\ProductCode;
use App\ShippingCostCalculator\Domain\Carrier;
use App\Warehouse\Domain\CostStrategy\WarehouseCostStrategyInterface;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Infrastructure\API\KpyPublicApi;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;

readonly class Warehouse
{
    public function __construct(
        private int                            $id,
        private string                         $name,
        private WarehouseCostStrategyInterface $costStrategy,
        private Carrier                        $carrier,
        private PackagingHandler               $packagingHandler,
        private WarehouseProductRepository     $warehouseProductRepository,
        private KpyPublicApi                   $kpyPublicApi,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getManipulationCost(Product $product, int $quantity = 1): float
    {
        return round($this->costStrategy->computeManipulationCost($product, $quantity), 6);
    }

    public function getCarrier(): ?Carrier
    {
        return $this->carrier;
    }

    public function getDefaultDestination(): Destination
    {
        return Destination::PENINSULA;
    }

    public function getPackagingHandler(): PackagingHandler
    {
        return $this->packagingHandler;
    }

    /**
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        $productsWarehouseEntities = $this->warehouseProductRepository->findProductsInWarehouse($this);

        $products = $this->kpyPublicApi->getAllProductsBySKU();

        $warehouseProducts = [];

        foreach ($productsWarehouseEntities as $productsWarehouseEntity) {
            $productCode = ProductCode::from($productsWarehouseEntity->getProductId(), $productsWarehouseEntity->getProductAttributeId());
            /** @var \App\Shared\Domain\ValueObject\Product $product */
            $product = $products[$productCode->getSku()] ?? null;

            // al subir las tarifas es posible que se haya metido algún producto/combinación eliminada que se ha quedado ahí...
            if (!$product) {
                continue;
            }

            $warehouseProducts[] = new Product(
                $productCode,
                $product->getBrandId(),
                $product->getWeight(),
                $productsWarehouseEntity->getFinalCostPrice()
            );
        }

        return $warehouseProducts;
    }
}
