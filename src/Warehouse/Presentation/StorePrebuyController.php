<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Service\Policy\Context\RequestPolicyContext;
use App\Shared\Domain\Service\Policy\MandatoryParamsRequestPolicy;
use App\Shared\Domain\Service\Policy\RequestPolicy;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Service\OrderPrebuyProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prebuy', host: 'app.%kpy.base_domain%', name: 'warehouse_prebuy_')]
class StorePrebuyController extends AbstractController
{
    public function __construct(
        private readonly OrderPrebuyProvider   $orderPrebuyProvider,
        private readonly JsonResponseGenerator $jsonResponseGenerator)
    {
    }

    #[Route(name: 'list')]
    public function index(): Response
    {
        try {
            return $this->render('warehouse/prebuy/list.html.twig', [
                'orders' => $this->orderPrebuyProvider->getOrdersPendingConfirmationList(),
            ]);

        } catch (KpyException $exception) {
            return $this->render('error.html.twig', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    #[Route('/orderdetail', name: 'details', methods: 'POST')]
    public function details(Request $request, MandatoryParamsRequestPolicy $mandatoryParamsPolicy): Response
    {
        try {
            $mandatoryParamsPolicy->apply(new RequestPolicyContext(
                $request,
                [RequestPolicy::MANDATORY_PARAMS => ['operation', 'order_id']])
            );

            $products = $this->orderPrebuyProvider->getProductsByOperation((int)$request->request->get('operation'));

            return $this->jsonResponseGenerator->success([
                'html' => $this->renderView('warehouse/prebuy/_order-details.html.twig', [
                    'order_id' => $request->request->get('order_id'),
                    'products' => $products,
                ])
            ]);

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }

    #[Route('/orderchecked/{orderId}', name: 'order_checked', requirements: ['orderId' => '\d+'], methods: 'DELETE')]
    public function orderChecked(int $orderId, CommandBus $bus): Response
    {
        try {
            $bus->execute('kpy.warehouse.command.prebuy_order_reviewed', [
                'order' => $orderId,
            ]);

            return $this->jsonResponseGenerator->success();

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }

    #[Route('/produts', name: 'products', methods: 'POST')]
    public function addProducts(Request $request, CommandBus $bus): Response
    {
        try {
            $products = $request->toArray();

            if (empty($products)) {
                return $this->jsonResponseGenerator->success();
            }

            foreach ($products as $product) {
                $bus->execute('kpy.warehouse.command.prebuy_product_add', [
                    'sku' => $product['sku'],
                    'quantity' => $product['quantity'],
                ]);
            }

            return $this->jsonResponseGenerator->success();

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }
}
