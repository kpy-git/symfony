<?php

namespace App\Connectif\Service;

use App\Connectif\Infrastructure\Api\ConnectifAPI;
use App\Connectif\ProductInfoProvider;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;

class ProductSynchronizer
{
    private array $productFeatures;

    public function __construct(
        private readonly ProductInfoProvider $productProvider,
        private readonly ConnectifAPI        $connectifAPI
    )
    {
        $this->productFeatures = [];
    }

    public function syncProductByCode(ProductCode $productCode, Shop $shop): void
    {

    }

    /**
     * @throws KpyQueryNotFoundException
     */
    private function loadAdditionalRequiredInfo(): void
    {
        $this->productFeatures = $this->productProvider->productFeatures();

    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function syncAllProducts(Shop $shop): void
    {
        $products = $this->productProvider->findProductsForSync();
        $this->loadAdditionalRequiredInfo();
    }

    public function syncProductsFilterBy(Shop $shop, array $filters = []): void
    {
        $products = $this->productProvider->findProductsForSync($filters);
        $this->loadAdditionalRequiredInfo();
    }
}
