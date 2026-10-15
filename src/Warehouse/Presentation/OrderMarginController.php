<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Exception\KpyProductNotFoundException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\Warehouse\Infrastructure\Persistence\Repository\OrderMarginRepository;
use App\Warehouse\Query\QueryBus;
use App\Warehouse\Service\OrderMarginCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/margin', name: 'warehouse_order_margin_', host: 'ops.%kpy.base_domain%')]
class OrderMarginController extends AbstractController
{
    public function __construct(
        private readonly JsonResponseGenerator $jsonResponseGenerator
    )
    {
    }

    #[Route('/order/{orderId}', name: 'compute')]
    public function computeOrderMargin(
        int                   $orderId,
        OrderMarginCalculator $orderMarginCalculator,
        SerializerInterface   $serializer,
        OrderMarginRepository $orderMarginRepository
    ): JsonResponse
    {
        try {
            $orderMargin = $orderMarginCalculator->computeMarginByOrder($orderId);

            $orderMarginRepository->save($orderMargin);

            return $this->jsonResponseGenerator->success([
                'orderMargin' => json_decode($serializer->serialize($orderMargin, 'json'), true),
            ]);

        } catch (KpyException $kpyException) {
            return $this->jsonResponseGenerator->fromException($kpyException);
        }
    }

    #[Route('/missingorders', name: 'missing_orders')]
    public function computeMissingOrdersMargins(
        Request               $request,
        QueryBus              $queryBus,
        OrderMarginCalculator $orderMarginCalculator,
        OrderMarginRepository $orderMarginRepository
    ): JsonResponse
    {
        try {
            $params = [];
            if ($request->query->has('limit')) {
                $params['limit'] = $request->query->get('limit');
            }

            $orders = $queryBus->fetch('kpy.warehouse.query.missing_orders_margin', $params);

            if (empty($orders)) {
                return $this->jsonResponseGenerator->success([
                    'msg' => 'No orders found without margin calculated',
                ]);
            }

            $errors = [];
            $calculated = 0;

            foreach ($orders as $order) {
                $orderId = (int)$order['NUMERO_DOC'];

                try {
                    $orderMargin = $orderMarginCalculator->computeMarginByOrder($orderId);
                    $orderMarginRepository->save($orderMargin);
                    $calculated ++;

                } catch (KpyProductNotFoundException $exception) {
                    $errors[] = $exception->getMessage() . ", order " . $orderId;

                } catch (ShippingCostException $exception) {
                    $errors[] = $exception->getMessage() . ", order " . $orderId;
                }
            }

            return $this->jsonResponseGenerator->success([
                'msg' => $calculated . ' orders margins calculated',
                'errors' => $errors,
            ]);

        } catch (KpyException $kpyException) {
            return $this->jsonResponseGenerator->fromException($kpyException);
        }
    }
}
