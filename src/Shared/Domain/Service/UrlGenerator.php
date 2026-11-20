<?php

namespace App\Shared\Domain\Service;

use App\Shared\Bus\Query\KpyQueryBus;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;

readonly class UrlGenerator
{
    public function __construct(private KpyQueryBus $queryBus)
    {
    }

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

    public function generateProductLink(ProductCode $productCode, Shop $shop = Shop::KOMPY_ES): string
    {
        $rewrites = $this->queryBus->fetch('kpy.shared.query.rewrite_elements_by_product', [
            'id_product' => $productCode->getProductId(),
            'id_lang' => $shop->getLanguageId(),
            'id_shop' => $shop->getId(),
        ]);

        return $this->getProductLink($productCode, $shop, $rewrites['category_rewrite'], $rewrites['product_rewrite']);
    }
}
