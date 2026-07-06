<?php

namespace App\Priceshape\Infrastructure\Controller;

use App\Priceshape\Domain\ProductProvider;
use App\Shared\Domain\Service\JsonResponseGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/priceshape', host: 'ops.%kpy.base_domain%', name: 'priceshape_')]
class FeedController extends AbstractController
{
    public function __construct(
        private readonly JsonResponseGenerator $jsonResponseGenerator,
    )
    {
    }

    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function generateFeed(
        ProductProvider $productProvider,
    ): JsonResponse
    {
        return $this->jsonResponseGenerator->success([
            'data' => $productProvider->getProductsByShop()
        ]);
    }
}
