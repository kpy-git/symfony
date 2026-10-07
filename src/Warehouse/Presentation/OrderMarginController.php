<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Warehouse\Service\OrderMarginCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/margin', name: 'warehouse_order_margin_', host: 'app.%kpy.base_domain%')]
class OrderMarginController extends AbstractController
{
    public function __construct(
        private readonly JsonResponseGenerator $jsonResponseGenerator
    )
    {
    }

    #[Route('/order/{orderId}', name: 'compute')]
    public function computeOrderMargin(int                   $orderId,
                                       OrderMarginCalculator $orderMarginCalculator,
                                       SerializerInterface   $serializer
    ): JsonResponse
    {
        $margin = $orderMarginCalculator->computeMarginByOrder($orderId);

        return $this->jsonResponseGenerator->success([
            'margin' => json_decode($serializer->serialize($margin, 'json'), true),
        ]);
    }
}
