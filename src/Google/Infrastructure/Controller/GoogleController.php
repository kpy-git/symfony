<?php

namespace App\Google\Infrastructure\Controller;

use App\Google\Domain\Exception\KpyGoogleException;
use App\Google\Service\GoogleMerchantFeedHandler;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Shop;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/google", host: 'ops.%kpy.base_domain%', name: 'ops_google_')]
final class GoogleController extends AbstractController
{
    public function __construct(private readonly JsonResponseGenerator $responseGenerator)
    {
    }

    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function feed(
        Request $request,
        GoogleMerchantFeedHandler $feedHandler
    ): JsonResponse
    {
        if (!$request->query->has('token')) {
            return $this->responseGenerator->error('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $feedHandler->syncFeed(Shop::KOMPY_ES);

            return $this->responseGenerator->success([
                'previous_products' => $feedHandler->totalPreviousProducts(),
                'current_products' => $feedHandler->totalCountProducts()
            ]);

        } catch (KpyGoogleException $exception) {
            return $this->responseGenerator->fromException($exception);
        }
    }
}
