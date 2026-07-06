<?php

namespace App\Google\Infrastructure\API;

use App\Shared\Domain\ValueObject\ProductCode;

interface GooglePublicApiInterface
{
    public function getSuggestedInfoByProduct(ProductCode $productCode): array;

    public function getAllProductSuggestedInfo(string $countryISOCode): array;
}
