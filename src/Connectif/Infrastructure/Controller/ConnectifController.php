<?php

namespace App\Connectif\Infrastructure\Controller;

use App\Connectif\Service\ProductSynchronizer;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\ValueObject\ProductCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connectif', host: 'ops.%kpy.base_domain%', name: 'connectif_')]
final class ConnectifController extends AbstractController
{
    #[Route('/products', name: 'sync_products', methods: ['GET'])]
    public function index(
        JsonResponseGenerator $jsonResponseGenerator,
        ProductSynchronizer $synchronizer
    ): JsonResponse
    {
        try {
            //$synchronizer->syncProductByCode(ProductCode::fromSKU('1124-0'));
            $synchronizer->syncAllProducts();

            return $this->json([
                'status' => 200,
            ]);

        } catch (KpyException $exception) {
            return $jsonResponseGenerator->fromException($exception);
        }
    }
}
