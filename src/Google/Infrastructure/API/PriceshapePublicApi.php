<?php

namespace App\Google\Infrastructure\API;

use App\Priceshape\Infrastructure\API\PriceshapePublicApiInterface;

readonly class PriceshapePublicApi implements PriceshapePublicApiInterface
{
    public function __construct(private PriceshapePublicApiInterface $priceshapePublicApi)
    {
    }

    public function getProductsInfoByCountry(string $countryISO): array
    {
        return $this->priceshapePublicApi->getProductsInfoByCountry($countryISO);
    }
}
