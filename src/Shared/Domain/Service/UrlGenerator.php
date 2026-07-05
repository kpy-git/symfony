<?php

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;

class UrlGenerator
{
    public function getProductLink(
        ProductCode $productCode,
        Shop        $shop,
        string      $categoryRewrite,
        string      $productRewrite): string
    {
        return "https://{$shop->getDomain()}/{$categoryRewrite}/{$productCode->getCodeForUrl()}-{$productRewrite}.html";
    }

    public function getImageLink(
        int    $imageId,
        Shop   $shop,
        string $linkRewrite,
        string $format = 'large_default'
    ): string
    {
        return "https://{$shop->getDomain()}/{$imageId}-{$format}/{$linkRewrite}.jpg";
    }
}
