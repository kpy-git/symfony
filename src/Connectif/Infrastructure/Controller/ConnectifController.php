<?php

namespace App\Connectif\Infrastructure\Controller;

use App\Connectif\Service\ProductSynchronizer;
use App\Connectif\Service\PurchaseSynchronizer;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\ValueObject\ProductCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connectif', host: 'ops.%kpy.base_domain%', name: 'connectif_')]
final class ConnectifController extends AbstractController
{
    public function __construct(
        private readonly JsonResponseGenerator $jsonResponseGenerator,
    )
    {
    }

    #[Route('/product/{sku}', name: 'sync_product', methods: ['GET'])]
    public function syncProduct(
        string $sku,
        ProductSynchronizer $synchronizer
    ): JsonResponse
    {
        try {
            $synchronizer->syncProductByCode(ProductCode::fromSKU($sku));

            return $this->json([
                'status' => 200,
            ]);

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }

    #[Route('/products', name: 'sync_products', methods: ['GET'])]
    public function syncProducts(
        ProductSynchronizer $synchronizer
    ): JsonResponse
    {
        try {
            $synchronizer->syncAllProducts();

            return $this->json([
                'status' => 200,
            ]);

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }

    #[Route('/order/{order}', name: 'sync_order', methods: ['GET'])]
    public function syncOrder(int $order, PurchaseSynchronizer $synchronizer): JsonResponse
    {
        try {
            $synchronizer->syncPurchase($order);

            return $this->jsonResponseGenerator->success();

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }

    #[Route('/orders/{fromOrder}', name: 'sync_orders', methods: ['GET'])]
    public function syncPurchasesFrom(int $fromOrder, PurchaseSynchronizer $synchronizer): JsonResponse
    {
        try {
            return $this->jsonResponseGenerator->success([
                'results' => $synchronizer->syncPurchasesFrom($fromOrder)
            ]);

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }
}
