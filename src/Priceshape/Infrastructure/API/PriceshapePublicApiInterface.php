<?php

namespace App\Priceshape\Infrastructure\API;

interface PriceshapePublicApiInterface
{
    public function getProductsInfoByCountry(string $countryISO): array;
}
