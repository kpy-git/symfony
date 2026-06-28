<?php

namespace App\Google\Infrastructure\Controller;

use App\Google\Service\GoogleMerchantFeedHandler;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/google", name: 'google_')]
final class GoogleController extends AbstractController
{
    public function __construct(private readonly JsonResponseGenerator $responseGenerator)
    {
    }

    #[Route('/feed', host: 'ops.%kpy.base_domain%', name: 'feed', methods: ['GET'])]
    public function feed(
        GoogleMerchantFeedHandler $feedHandler
    ): JsonResponse
    {
        try {
            $feedHandler->syncFeed(Shop::KOMPY_ES);

            return $this->responseGenerator->success([
                'previous_products' => $feedHandler->totalPreviousProducts(),
                'current_products' => $feedHandler->totalCountProducts()
            ]);

        } catch (KpyException $exception) {
            return $this->responseGenerator->fromException($exception);
        }
    }
}
