<?php

namespace App\Warehouse\Infrastructure\API;

use App\Shared\Domain\ValueObject\Product;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\API\KpyPublicApiInterface;

readonly class KpyPublicApi implements KpyPublicApiInterface
{

    public function __construct(private KpyPublicApiInterface $kpyPublicApi)
    {
    }

    public function getProduct(ProductCode $productCode): Product
    {
        return $this->kpyPublicApi->getProduct($productCode);
    }

    public function getProductWeight(ProductCode $productCode): float
    {
        return $this->kpyPublicApi->getProductWeight($productCode);
    }

    public function getAllProductsWeights(): array
    {
        return $this->kpyPublicApi->getAllProductsWeights();
    }

    public function getProductBrandId(ProductCode $productCode): int
    {
        return $this->kpyPublicApi->getProductBrandId($productCode);
    }

    public function getAllProductsBySKU(): array
    {
        return $this->kpyPublicApi->getAllProductsBySKU();
    }
}
