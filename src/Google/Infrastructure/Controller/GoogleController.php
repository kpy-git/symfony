<?php

namespace App\Google\Infrastructure\Controller;

use App\Shared\Domain\Aggregate\Destination;
use App\ShippingCostCalculator\Domain\Builder\CarrierBuilder;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/google", host: 'ops.%kpy.base_domain%', name: 'ops_google_')]
final class GoogleController extends AbstractController
{
    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function index(
        Request $request,
        CarrierBuilder $carrierBuilder,
        CalculatorShippingCost $calculatorShippingCost
    ): JsonResponse
    {
        if (!$request->query->has('token')) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $carrier = $carrierBuilder->getMRW();

        if ($request->query->get('weight')) {
            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'cost' => $calculatorShippingCost->getShippingCostBy($carrier, Destination::PENINSULA, (float)$request->query->get('weight')),
            ]);
        }

        return $this->json([
            'success' => true,
            'ranges' => [
                $carrier->getRangesByDestination(Destination::PENINSULA),
                $carrier->getRangeAdditionalPerKgByDestination(Destination::PENINSULA),
                ]
        ]);
    }
}
