<?php

namespace App\Priceshape\Domain;

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
    private array $productsImage;

    /**
     * @throws KpyQueryNotFoundException
     */
    public function __construct(
        private readonly KpyQueryBus            $kpyQueryBus,
        private readonly QueryBus               $queryBus,
        private readonly CalculatorShippingCost $calculatorShippingCost,
        private readonly UrlGenerator           $urlGenerator,
        #[Autowire(service: 'mrw')] Carrier     $mrw,
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
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function getProductsByShop(Shop $shop = Shop::KOMPY_ES): array
    {

        $prestashopProducts = $this->queryBus->fetch('kpy.priceshape.query.prestashop_products');

        $products = [];

        foreach ($prestashopProducts as $prestashopProduct) {
            $productCode = ProductCode::from($prestashopProduct['id_product'], $prestashopProduct['id_product_attribute']);
            $sku = $productCode->getSku();

            if (!isset($this->aquaProducts[$sku])) {
                continue;
            }

            $product = new Product();
            $product
                ->setSku($sku)
                ->setTitle($prestashopProduct['name'])
                ->setBrand(str_replace('´', "'", $prestashopProduct['fabricante']))
                ->setVAT((int)$prestashopProduct['iva'])
                ->setSalePrice($this->productsPrices[$sku]['sales_price'])
                ->setCostPrice(round($this->productsPrices[$sku]['cost_price'] * 1.06, 2))
                ->setGtin($this->aquaProducts[$sku]['EAN'] ?? '')
                ->setStockGroup($this->aquaProducts[$sku]['GRUPO'])
                ->setAvailability((int)$this->aquaProducts[$sku]['STOCK'] <= 0 ? 'out_of_stock' : 'in_stock')
                ->setShippingPrice($this->calculatorShippingCost->calculateShippingCostByWeightWithSavedConfiguration((float)$this->aquaProducts[$sku]['PESO']))
                ->setWeight((float)$this->aquaProducts[$sku]['PESO'])
                ->setMpn($this->aquaProducts[$sku]['REFERENCIA'] ?? '')
                ->setProductLink($this->urlGenerator->getProductLink($productCode, $shop, $prestashopProduct['category_rewrite'], $prestashopProduct['product_rewrite']))
                ->setImageUrl($this->urlGenerator->getImageLink($this->productsImage[$productCode->getProductId()], $shop, $prestashopProduct['product_rewrite']))
                ->setPet($prestashopProduct['mascota'] ?? 'Perro')
                ->setProductType($this->computeProductType($this->aquaProducts[$sku]['GRUPOLOGISTICO'], $prestashopProduct['antiparasitario'], $prestashopProduct['mascota'] ?? 'Perro'))
                ->setCategory($mainCategories[$prestashopProduct['id_product']] ?? '')
                ->setSalesLast30Days((int)$this->aquaProducts[$sku]['VENTAS_30'])
                ->setBrandRanking((int)$this->aquaProducts[$sku]['BRAND_RANKING'])
                ->setFixedPrice(in_array($sku, $this->productsWithFixedPrice) || in_array($prestashopProduct['id_manufacturer'], $this->brandsWithFixedPrice))
                ->setBuyers($customersBySku[$sku] ?? 0);

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
}
