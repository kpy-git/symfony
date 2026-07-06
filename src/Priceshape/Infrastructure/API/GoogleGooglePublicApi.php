<?php

namespace App\Priceshape\Infrastructure\API;

use App\Google\Infrastructure\API\GooglePublicApiInterface;
use App\Shared\Domain\ValueObject\ProductCode;

readonly class GoogleGooglePublicApi implements GooglePublicApiInterface
{
    public function __construct(private GooglePublicApiInterface $googlePublicApi)
    {
    }

    public function getSuggestedInfoByProduct(ProductCode $productCode): array
    {
        return $this->googlePublicApi->getSuggestedInfoByProduct($productCode);
    }

    public function getAllProductSuggestedInfo(string $countryISOCode): array
    {
        return $this->googlePublicApi->getAllProductSuggestedInfo($countryISOCode);
    }
}
