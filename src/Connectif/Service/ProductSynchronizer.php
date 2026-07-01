<?php

namespace App\Connectif\Service;

use App\Connectif\Command\ConnectifCommandBus;
use App\Connectif\ConnectifAPIException;
use App\Connectif\ConnectifException;
use App\Connectif\Infrastructure\Api\ConnectifAPI;
use App\Connectif\Product;
use App\Connectif\ProductInfoProvider;
use App\Shared\Bus\Command\KpyCommandNotFoundException;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\Service\CategoriesBreadcrumbGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

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
        #[Autowire('%connectif.log_dir%')]
        private readonly string                        $logDir,

    )
    {
    }

    /**
     * @throws ConnectifAPIException
     * @throws KpyInvalidProductCode
     * @throws KpyCommandNotFoundException
     * @throws ConnectifException
     * @throws KpyQueryNotFoundException
     * @throws \JsonException
     */
    public function syncProductByCode(ProductCode $productCode, Shop $shop = Shop::KOMPY_ES): void
    {
        $productRaw = $this->provider->findProductsForSync([
            'product_code' => $productCode,
        ])[0] ?? null;

        if (!$productRaw) {
            throw new ConnectifException('No se ha encontrado ningún resultado con los filtros dados');
        }
        $this->loadAdditionalRequiredInfo();

        try {
            $this->uploadProduct($productRaw, $shop);

        } catch (ConnectifAPIException $ex) {
            $this->logger->error($ex->getMessage());
            new Filesystem()->dumpFile(
                $this->logDir . '/connectif_api_exception.json',
                json_encode($this->connectifAPI->getRequestsDetails(), JSON_PRETTY_PRINT)
            );

            throw new ConnectifAPIException($ex->getMessage());
        }
    }


    /**
     * @throws KpyQueryNotFoundException
     */
    private function loadAdditionalRequiredInfo(): void
    {
        $this->productFeatures = $this->provider->featuresByProductId();
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
        $this->syncProducts($this->provider->findProductsForSync(), $shop);
    }

    /**
     * @throws KpyInvalidProductCode
     * @throws KpyCommandNotFoundException
     * @throws KpyQueryNotFoundException
     * @throws \JsonException
     */
    private function syncProducts(array $products, Shop $shop): void
    {
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
                $this->uploadProduct($productRaw, $shop);

            } catch (ConnectifAPIException $ex) {
                new Filesystem()->appendToFile(
                    $this->logDir . '/connectif_api_exception.json',
                    json_encode($this->connectifAPI->getRequestsDetails(), JSON_PRETTY_PRINT)
                );

                $this->logger->error($ex->getMessage());
            }
        }
    }

    /**
     * @throws ConnectifAPIException
     * @throws KpyInvalidProductCode
     * @throws KpyCommandNotFoundException
     * @throws \JsonException
     */
    private function uploadProduct(array $productRaw, Shop $shop): void
    {
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
            throw new ConnectifAPIException($this->connectifAPI->getResponse());
        }

        $this->commandBus->execute('kpy.connectif.command.product_synchronized', [
            'product_code' => ProductCode::fromSKU($productRaw['sku']),
        ]);
    }

    public function syncProductsFilterBy(Shop $shop = Shop::KOMPY_ES, array $filters = []): void
    {
        $this->syncProducts($this->provider->findProductsForSync($filters), $shop);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
