<?php

namespace App\Priceshape\Domain;

use App\Google\Infrastructure\API\GooglePublicApiInterface;
use App\Priceshape\Domain\ValueObject\Brand;
use App\Priceshape\Query\QueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Destination;
use App\Shared\Domain\Service\UrlGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\API\KpyPublicApi;
use App\Warehouse\Domain\ValueObject\WarehouseProductFulfillmentCost;
use App\Warehouse\Infrastructure\API\WarehousePublicApiInterface;

class ProductProvider
{
    private array $aquaProducts;
    private array $productsPrices;
    private array $productsWithFixedPrice;
    private array $brandsWithFixedPrice;
    private array $productsImage;
    private array $suggestedRetailPrices;
    private array $featuresGroupByProduct;
    private array $mainCategories;
    private array $productsExcluded;
    private array $prestashopProducts;
    private array $googleInfoBySku;

    public function __construct(
        private readonly KpyPublicApi                $sharedApi,
        private readonly QueryBus                    $queryBus,
        private readonly UrlGenerator                $urlGenerator,
        private readonly GooglePublicApiInterface    $googleApi,
        private readonly WarehousePublicApiInterface $warehouseApi,
    )
    {
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function getProductsByShop(Shop $shop = Shop::KOMPY_ES): array
    {
        $this->loadRequiredData($shop);

        $products = [];

        foreach ($this->prestashopProducts as $prestashopProduct) {
            $productCode = ProductCode::from($prestashopProduct['id_product'], $prestashopProduct['id_product_attribute']);
            $sku = $productCode->getSku();

            if (!isset($this->aquaProducts[$sku], $this->productsPrices[$sku]['sales_price'], $this->productsPrices[$sku]['fulfillment_price'])) {
                $this->productsExcluded[] = $sku;
                continue;
            }

            $product = new Product();

            $salesPrice = $this->productsPrices[$sku]['sales_price'];

            $shipping_price = $salesPrice > $shop->priceLimitToShippingFree() ? 0 : $shop->shippingPriceByDestination(Destination::PENINSULA);

            $product
                ->setSku($sku)
                ->setTitle($prestashopProduct['name'])
                ->setBrand(str_replace('´', "'", $prestashopProduct['fabricante']))
                ->setVAT((int)$prestashopProduct['iva'])
                ->setSalePrice($salesPrice)
                ->setCostPrice(round($this->productsPrices[$sku]['fulfillment_price'], 2))
                ->setGtin($this->aquaProducts[$sku]['EAN'] ?? '')
                ->setStockGroup($this->aquaProducts[$sku]['GRUPO'])
                ->setAvailability($prestashopProduct['stock'] <= 0 ? 'out_of_stock' : 'in_stock')
                ->setShippingPrice($shipping_price)
                ->setWeight((float)$this->aquaProducts[$sku]['PESO'])
                ->setMpn($this->aquaProducts[$sku]['REFERENCIA'] ?? '')
                ->setProductLink($this->urlGenerator->getProductLink($productCode, $shop, $prestashopProduct['category_rewrite'], $prestashopProduct['product_rewrite']))
                ->setImageUrl($this->urlGenerator->getImageLink($this->productsImage[$productCode->getProductId()], $shop, $prestashopProduct['product_rewrite']))
                ->setPet($prestashopProduct['mascota'] ?? 'Perro')
                ->setProductType($this->computeProductType($this->aquaProducts[$sku]['GRUPOLOGISTICO'], $prestashopProduct['antiparasitario'], $prestashopProduct['mascota'] ?? 'Perro'))
                ->setCategory($this->mainCategories[$prestashopProduct['id_product']] ?? '')
                ->setSalesLast30Days((int)$this->aquaProducts[$sku]['VENTAS_30'])
                ->setBrandRanking((int)$this->aquaProducts[$sku]['BRAND_RANKING'])
                ->setFixedPrice(in_array($sku, $this->productsWithFixedPrice) || in_array($prestashopProduct['id_manufacturer'], $this->brandsWithFixedPrice, true))
                ->setBuyers($customersBySku[$sku] ?? 0);

            if (isset($this->suggestedRetailPrices[$sku])) {
                $propertyName = 'suggestedRetailPrice';
                $product->$propertyName = $this->suggestedRetailPrices[$sku];
            }

            if (array_key_exists($prestashopProduct['id_product'], $this->featuresGroupByProduct)) {
                foreach ($this->featuresGroupByProduct[$prestashopProduct['id_product']] as $feature => $value) {
                    if (!strcasecmp(trim($feature), 'Tipo de producto')) {
                        $product->setProductType($value);
                    } else {
                        $product->$feature = $value;
                    }
                }
            }

            if ($prestashopProduct['special_discount'] > 0) {
                $product->setPromoType('Special price');
                $product->setPromoValue(number_format($prestashopProduct['special_discount']) . '%');
            } /*else if ($prestashopProduct['cupon'] !== '') {
                $product->setPromoType('Cupon');
                $product->setPromoValue($prestashopProduct['cupon']);
            } else if ($prestashopProduct['regalo'] !== '') {
                $product->setPromoType('Regalo');
                $product->setPromoValue($prestashopProduct['regalo']);
            }*/

            if (isset($this->googleInfoBySku[$sku])) {
                foreach ($this->googleInfoBySku[$sku] as $key => $value) {
                    $product->$key = $value;
                }
            }

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    private function loadRequiredData(Shop $shop): void
    {
        /** @var Brand[] $brands */
        $brands = $this->queryBus->fetch('kpy.priceshape.query.brands_included');

        $this->brandsWithFixedPrice = array_map(
            static fn (Brand $brand): int => $brand->getManufacturerId(),
            array_filter($brands, static fn (Brand $brand): bool => $brand->isWithFixedPrice())
        );

        $this->prestashopProducts = $this->queryBus->fetch('kpy.priceshape.query.prestashop_products', [
            'brands' => array_map(static fn (Brand $brand): int => $brand->getManufacturerId(), $brands),
        ]);

        $this->googleInfoBySku = $this->googleApi->getAllProductSuggestedInfo($shop->getDefaultCountry()->getISO());

        $this->aquaProducts = array_reduce(
            $this->queryBus->fetch('kpy.priceshape.query.aqua_products_info'),
            static function (array $carry, array $row): array {
                $carry[$row['SKU']] = $row;
                return $carry;
            }, []
        );


        $productsFulfillmentPrices = $this->warehouseApi->getDefaultFulfillmentCostsIndexByProduct();

        /** @var WarehouseProductFulfillmentCost $productFulfillmentPrice */
        foreach ($productsFulfillmentPrices as $productFulfillmentPrice) {
            $this->productsPrices[$productFulfillmentPrice->getProductCode()->getSku()]['fulfillment_price'] = $productFulfillmentPrice->getFulfillmentCost();
        }

        $productSalesPrices = $this->sharedApi->getProductSalesPricesByShop($shop);

        foreach ($productSalesPrices as $sku => $productSalesPrice) {
            if (isset($this->productsPrices[$sku])) {
                $this->productsPrices[$sku]['sales_price'] = $productSalesPrice;
            }
        }

        $this->productsWithFixedPrice = array_map(
            static fn(array $row) => ProductCode::from($row['id_product'], $row['id_product_attribute'])->getSku(),
            $this->queryBus->fetch('kpy.priceshape.query.products_with_fixed_price')
        );

        $this->productsImage = $this->sharedApi->getAllFirstImagesIndexByProduct();


        $this->suggestedRetailPrices = array_reduce(
            $this->queryBus->fetch('kpy.priceshape.query.suggested_retail_prices'),
            static function (array $carry, array $row): array {
                $carry[ProductCode::from($row['id_product'], $row['id_product_attribute'])->getSku()] = $row['pvpr'];
                return $carry;
            }, []
        );

        $this->featuresGroupByProduct = array_reduce(
            $this->queryBus->fetch('kpy.priceshape.query.product_features_group_by_product'),
            static function (array $carry, array $row): array {
                $carry[$row['id_product']][$row['feature']] = $row['value'];
                return $carry;
            }, []
        );

        $this->mainCategories = array_reduce(
            $this->queryBus->fetch('kpy.priceshape.query.main_categories'),
            static function (array $carry, array $row): array {
                if (!array_key_exists($row['id_product'], $carry)) {
                    $carry[$row['id_product']] = $row['name'];
                }
                return $carry;
            }, []
        );
    }

    private function computeProductType(string $logisticGroup, string $antiparasitario, string $pet): string
    {
        if ($antiparasitario === 'si') {
            return 'Antiparasitarios';
        }

        if (trim($logisticGroup) === 'BOSKE'
            || stripos(trim($logisticGroup), 'saco') !== false
            || stripos(trim($logisticGroup), 'GRANDE') !== false
            || stripos(trim($logisticGroup), 'MEDIANO') !== false
            || stripos(trim($logisticGroup), 'PEQUE') !== false
            || stripos(trim($logisticGroup), 'MINI') !== false
            || stripos(trim($logisticGroup), 'HUMEDO') !== false
            || stripos(trim($logisticGroup), 'SNACK') !== false
        ) {
            return sprintf('Alimentacion %s', $pet);
        }

        if (trim($logisticGroup) === 'FARMACOLOGICOS'
            || trim($logisticGroup) === 'AUDEVARD'
            || trim($logisticGroup) === 'CHAMPU'
        ) {
            return 'Farmacologicos';
        }

        return 'Accesorios';
    }

    /**
     * @return array
     */
    public function getProductsExcluded(): array
    {
        return $this->productsExcluded;
    }
}
