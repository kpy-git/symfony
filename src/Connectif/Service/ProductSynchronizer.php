<?php

namespace App\Connectif\Service;

use App\Connectif\Command\ConnectifCommandBus;
use App\Connectif\ConnectifAPIExcection;
use App\Connectif\ConnectifException;
use App\Connectif\Infrastructure\Api\ConnectifAPI;
use App\Connectif\Product;
use App\Connectif\ProductInfoProvider;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\Service\CategoriesBreadcrumbGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ProductSynchronizer implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private array $productFeatures = [];

    private array $relatedProducts = [];

    private array $productsRatingByProductId = [];

    private array $firstImageByProductId = [];

    private array $tagsByProductId = [];

    private array $salesPricesByProductId = [];

    public function __construct(
        private readonly ProductInfoProvider           $provider,
        private readonly CategoriesBreadcrumbGenerator $categoriesBreadcrumbGenerator,
        private readonly ConnectifAPI                  $connectifAPI,
        private readonly ConnectifCommandBus           $commandBus,
    )
    {
    }

    public function syncProductByCode(ProductCode $productCode, Shop $shop = Shop::KOMPY_ES): void
    {
        $productRaw = $this->provider->findProductsForSync([
            'product_code' => $productCode,
        ])[0] ?? null;

        if (!$productRaw) {
            throw new ConnectifException('No se ha encontrado ningún resultado con los filtros dados');
        }
        $this->loadAdditionalRequiredInfo();

        $product = Product::fromRow(
            $productRaw,
            $this->categoriesBreadcrumbGenerator->getAllCategoriesBreadcrumbByProduct($productRaw['id_product']),
            $this->productFeatures[$productRaw['id_product']] ?? [],
            $this->relatedProducts[$productRaw['id_product']] ?? [],
            $this->productsRatingByProductId[$productRaw['id_product']] ?? [],
            $this->tagsByProductId[$productRaw['id_product']] ?? [],
            $this->salesPricesByProductId[$productRaw['sku']],
            $this->provider->getCombinationImageId($productRaw['id_product_attribute']) ?: $this->firstImageByProductId[$productRaw['id_product']] ?? 0,
            $shop
        );


        if (!$this->connectifAPI->updateProduct($product)) {
            throw new ConnectifException("No se puede actualizar el producto.\n" . $this->connectifAPI->getResponse());
        }
        $this->logger->info(json_encode($this->connectifAPI->getRequestsDetails()));

        $this->commandBus->execute('kpy.connectif.command.product_synchronized', [
            'product_code' => $productCode,
        ]);


    }


    /**
     * @throws KpyQueryNotFoundException
     */
    private function loadAdditionalRequiredInfo(): void
    {
        $this->productFeatures = $this->provider->productFeatures();
        $this->relatedProducts = $this->provider->relatedProducts();
        $this->productsRatingByProductId = $this->provider->productsRatingByProductId();
        $this->firstImageByProductId = $this->provider->firstImageByProductId();
        $this->tagsByProductId = $this->provider->estacionalTagsByProductId();
        $this->salesPricesByProductId = $this->provider->getSalesPricesByProduct();
    }

    /**
     * @throws KpyQueryNotFoundException
     * @throws KpyInvalidProductCode
     */
    public function syncAllProducts(Shop $shop = Shop::KOMPY_ES): void
    {
        $products = $this->provider->findProductsForSync();
        $this->loadAdditionalRequiredInfo();

        foreach ($products as $productRaw) {
            if (empty($productRaw['name'])
                || empty($productRaw['link_producto'])
                || empty($productRaw['link_categoria'])
                || !isset($this->salesPricesByProductId[$productRaw['sku']])
            ) {
                continue;
            }

            try {
                $product = Product::fromRow(
                    $productRaw,
                    $this->categoriesBreadcrumbGenerator->getAllCategoriesBreadcrumbByProduct($productRaw['id_product']),
                    $this->productFeatures[$productRaw['id_product']] ?? [],
                    $this->relatedProducts[$productRaw['id_product']] ?? [],
                    $this->productsRatingByProductId[$productRaw['id_product']] ?? [],
                    $this->tagsByProductId[$productRaw['id_product']] ?? [],
                    $this->salesPricesByProductId[$productRaw['sku']],
                    $this->provider->getCombinationImageId($productRaw['id_product_attribute']) ?: $this->firstImageByProductId[$productRaw['id_product']] ?? 0,
                    $shop
                );

                if (!$this->connectifAPI->updateProduct($product)) {
                    throw new ConnectifAPIExcection($this->connectifAPI->getResponse());
                }

                $this->commandBus->execute('kpy.connectif.command.product_synchronized', [
                    'product_code' => ProductCode::fromSKU($productRaw['sku']),
                ]);

            } catch (ConnectifAPIExcection $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }

    public function syncProductsFilterBy(Shop $shop = Shop::KOMPY_ES, array $filters = []): void
    {
        $products = $this->provider->findProductsForSync($filters);
        $this->loadAdditionalRequiredInfo();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
