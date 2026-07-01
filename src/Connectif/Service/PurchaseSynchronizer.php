<?php

namespace App\Connectif\Service;

use App\Connectif\ConnectifAPIException;
use App\Connectif\ConnectifException;
use App\Connectif\Infrastructure\Api\ConnectifAPI;
use App\Connectif\ProductInfoProvider;
use App\Connectif\Purchase;
use App\Connectif\PurchaseProvider;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\Service\CategoriesBreadcrumbGenerator;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class PurchaseSynchronizer implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private array $allProductsSynchronized;

    private array $featuresByProductId;

    public function __construct(
        private readonly PurchaseProvider              $purchaseProvider,
        private readonly ConnectifAPI                  $connectifAPI,
        #[Autowire('%connectif.log_dir%')]
        private readonly string                        $logDir,
        private readonly Filesystem                    $filesystem,
        private readonly CategoriesBreadcrumbGenerator $categoriesBreadcrumbGenerator,
        private readonly ProductInfoProvider           $productInfoProvider
    )
    {
        $this->allProductsSynchronized = $this->productInfoProvider->getAllProductsSynchronized();

        $this->featuresByProductId = $this->productInfoProvider->featuresByProductId();
    }

    /**
     * @throws ConnectifAPIException
     * @throws KpyInvalidProductCode
     * @throws KpyQueryNotFoundException
     * @throws \JsonException
     * @throws ConnectifException
     */
    public function syncPurchase(int $orderId): void
    {
        $orderRaw = $this->purchaseProvider->order($orderId);

        if (empty($orderRaw)) {
            throw new ConnectifException('No existe ningún pedido con id ' . $orderId);
        }

        $this->uploadPurchase($orderRaw);
    }

    /**
     * @throws ConnectifAPIException
     * @throws KpyInvalidProductCode
     * @throws ConnectifException
     * @throws \JsonException
     */
    private function uploadPurchase(array $orderRaw): void
    {
        $productsRaw = $this->purchaseProvider->productsOrderPymLegacy($orderRaw['id_order']);

        $filteredProductsRaw = array_filter($productsRaw, fn(array $row) => in_array($row['SKU'], $this->allProductsSynchronized, true));

        if (empty($filteredProductsRaw)) {
            throw new ConnectifException('El pedido ' . $orderRaw['id_order'] . ' no tiene ningún producto válido para sincronizar');
        }

        foreach ($filteredProductsRaw as &$productRaw) {
            $productCode = ProductCode::fromSKU($productRaw['SKU']);
            $productRaw['categories'] = $this->categoriesBreadcrumbGenerator->getAllCategoriesBreadcrumbByProduct($productCode->getProductId());
            $productRaw['features'] = $this->featuresByProductId[$productCode->getProductId()] ?? [];
        }
        unset($productRaw);

        if (!$this->connectifAPI->createPurchase(Purchase::from($orderRaw, $filteredProductsRaw, Shop::KOMPY_ES))) {
            $this->filesystem->dumpFile(
                $this->logDir . '/purchase_request.json',
                json_encode($this->connectifAPI->getRequestsDetails(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            );
            throw new ConnectifAPIException('Error al crear el purchase.' . PHP_EOL . $this->connectifAPI->getResponse());
        }
    }

    public function syncPurchasesFrom(int $orderId): array
    {
        $countOk = 0;
        $countError = 0;
        $this->filesystem->remove($this->logDir . '/purchases_errors.json');

        /**
         * se obtiene un objeto PDOStatement para poder hacer un cursor, así no sacamos todas las filas de una vez
         */
        $stmt = $this->purchaseProvider->ordersFromPymLegacy($orderId);
        $stmt->execute();

        while ($order = $stmt->fetch()) {
            try {
                $this->filesystem->dumpFile($this->logDir . '/lastOrderVisited', $order['id_order']);
                $this->uploadPurchase($order);
                $countOk++;

            } catch (ConnectifAPIException $exception) {
                $this->filesystem->appendToFile($this->logDir . '/purchases_errors.json', $exception->getMessage());
                $countError++;

            } catch (ConnectifException $exception) {
                $this->logger->error($exception->getMessage());
                $countError++;
            }
        }

        return [
            'success' => $countOk,
            'error' => $countError
        ];
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
