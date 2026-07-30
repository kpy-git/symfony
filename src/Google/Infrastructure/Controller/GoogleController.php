<?php

namespace App\Google\Infrastructure\Controller;

use App\Google\Service\GoogleMerchantFeedHandler;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(host: 'ops.%kpy.base_domain%', name: 'google_')]
final class GoogleController extends AbstractController
{
    public function __construct(private readonly JsonResponseGenerator $responseGenerator)
    {
    }

    #[Route('/feed/google/{shop}', name: 'feed', methods: ['GET'])]
    public function feed(
        GoogleMerchantFeedHandler $feedHandler,
        Shop $shop = Shop::KOMPY_ES
    ): JsonResponse
    {
        try {
            $feedHandler->syncFeed($shop);

            return $this->responseGenerator->success([
                'previous_products' => $feedHandler->totalPreviousProducts(),
                'current_products' => $feedHandler->totalCountProducts()
            ]);

        } catch (KpyException $exception) {
            return $this->responseGenerator->fromException($exception);
        }
    }
}
