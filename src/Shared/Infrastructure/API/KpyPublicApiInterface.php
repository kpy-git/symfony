<?php

namespace App\Shared\Infrastructure\API;

use App\Shared\Domain\ValueObject\Product;
use App\Shared\Domain\ValueObject\ProductCode;

interface KpyPublicApiInterface
{
    public function getProductWeight(ProductCode $productCode): float;

    public function getAllProductsWeights(): array;

    public function getProductBrandId(ProductCode $productCode): int;

    public function getProduct(ProductCode $productCode): Product;

    public function getAllProductsBySKU(): array;
}
