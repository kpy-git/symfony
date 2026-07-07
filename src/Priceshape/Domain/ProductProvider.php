<?php

namespace App\Priceshape\Domain;

use App\Google\Infrastructure\API\GooglePublicApiInterface;
use App\Priceshape\Query\QueryBus;
use App\Shared\Bus\Query\KpyQueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Destination;
use App\Shared\Domain\Service\UrlGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductProvider
{
    private array $aquaProducts;
    private array $productsPrices;
    private array $productsWithFixedPrice;
    private array $brandsWithFixedPrice;
    private array $brandsBanned;
    private array $productsImage;
    private array $suggestedRetailPrices;
    private array $featuresGroupByProduct;
    private array $mainCategories;
    private array $productsExcluded = [];

    /**
     * @throws KpyQueryNotFoundException
     */
    public function __construct(
        private readonly KpyQueryBus              $kpyQueryBus,
        private readonly QueryBus                 $queryBus,
        private readonly CalculatorShippingCost   $calculatorShippingCost,
        private readonly UrlGenerator             $urlGenerator,
        #[Autowire(service: 'mrw')] Carrier       $mrw,
        private readonly GooglePublicApiInterface $googleApi,
    )
    {
        $this->calculatorShippingCost->setFixedCarrierAnDestination(
            $mrw, Destination::PENINSULA
        );

        $this->aquaProducts = array_reduce(
            $this->queryBus->fetch('kpy.priceshape.query.aqua_products_info'),
            static function (array $carry, array $row): array {
                $carry[$row['SKU']] = $row;
                return $carry;
            }, []
        );

        $this->productsPrices = array_reduce(
            $this->kpyQueryBus->fetch('kpy.priceshape.query.products_prices'),
            static function (array $carry, array $row): array {
                $carry[ProductCode::from($row['id_product'], $row['id_product_attribute'])->getSku()] = [
                    'cost_price' => $row['final_cost_price'],
                    'sales_price' => $row['sales_price_es'],
                ];
                return $carry;
            }, []
        );

        $this->productsWithFixedPrice = array_map(
            static fn(array $row) => ProductCode::from($row['id_product'], $row['id_product_attribute'])->getSku(),
            $this->queryBus->fetch('kpy.priceshape.query.products_with_fixed_price')
        );

        $this->brandsWithFixedPrice = array_map(
            static fn(array $row) => (int)$row['id_manufacturer'],
            $this->queryBus->fetch('kpy.priceshape.query.brands_with_fixed_price')
        );

        $this->productsImage = array_reduce(
            $this->kpyQueryBus->fetch('kpy.query.shared.product_images', ['only_first_image' => true]),
            static function (array $carry, array $row): array {
                $carry[$row['id_product']] = $row['id_image'];
                return $carry;
            }, []
        );

        $this->brandsBanned = array_map(
            static fn(array $row): int => (int)$row['id_manufacturer'],
            $this->queryBus->fetch('kpy.priceshape.query.brands_banned')
        );

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

    /**
     * @throws KpyQueryNotFoundException
     */
    public function getProductsByShop(Shop $shop = Shop::KOMPY_ES): array
    {
        $prestashopProducts = $this->queryBus->fetch('kpy.priceshape.query.prestashop_products', [
            'brands_banned' => $this->brandsBanned,
        ]);

        $googleInfoBySku = $this->googleApi->getAllProductSuggestedInfo($shop->getDefaultCountry()->getISO());

        $products = [];

        foreach ($prestashopProducts as $prestashopProduct) {
            $productCode = ProductCode::from($prestashopProduct['id_product'], $prestashopProduct['id_product_attribute']);
            $sku = $productCode->getSku();

            if (!isset($this->aquaProducts[$sku])) {
                $this->productsExcluded[] = $sku;
                continue;
            }

            $product = new Product();
            $shipping_price = $this->calculatorShippingCost->calculateShippingCostByWeightWithSavedConfiguration((float)$this->aquaProducts[$sku]['PESO']);
            $costPrice = round(($this->productsPrices[$sku]['cost_price'] * 1.06) + $shipping_price, 6);

            $product
                ->setSku($sku)
                ->setTitle($prestashopProduct['name'])
                ->setBrand(str_replace('´', "'", $prestashopProduct['fabricante']))
                ->setVAT((int)$prestashopProduct['iva'])
                ->setSalePrice($this->productsPrices[$sku]['sales_price'])
                ->setCostPrice(round($costPrice, 2))
                ->setGtin($this->aquaProducts[$sku]['EAN'] ?? '')
                ->setStockGroup($this->aquaProducts[$sku]['GRUPO'])
                ->setAvailability((int)$this->aquaProducts[$sku]['STOCK'] <= 0 ? 'out_of_stock' : 'in_stock')
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
                ->setFixedPrice(in_array($sku, $this->productsWithFixedPrice) || in_array($prestashopProduct['id_manufacturer'], $this->brandsWithFixedPrice))
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

            if (isset($googleInfoBySku[$sku])) {
                foreach ($googleInfoBySku[$sku] as $key => $value) {
                    $product->$key = $value;
                }
            }

            $products[] = $product;
        }

        return $products;
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
