<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;

readonly class Product extends \App\Shared\Domain\ValueObject\Product
{
    public function __construct(
        ProductCode   $productCode,
        int           $brand,
        float         $weight,
        private float $costPrice,
        float         $salesPrice,
    )
    {
        parent::__construct($productCode, $weight, $brand, $salesPrice);
    }

    public function getCostPrice(): float
    {
        return $this->costPrice;
    }

}
