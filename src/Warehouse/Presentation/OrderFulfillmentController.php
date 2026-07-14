<?php

namespace App\Warehouse\Presentation;

use App\Warehouse\Domain\OrderFactory;
use App\Warehouse\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//#[IsGranted("ROLE_WAREHOUSE")]
#[Route(host: 'warehouse.%kpy.base_domain%', name: 'warehouse_')]
final class OrderFulfillmentController extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    #[Route('/', name: 'fulfillment')]
    public function index(): Response
    {
        $pendingOrders = $this->queryBus->fetch('kpy.warehouse.query.pending_orders_kompychinales', [
            'state' => $_ENV['OWNERSHIP_WAREHOUSE_OS']
        ]);

        return $this->render('warehouse/fulfillment/index.html.twig', [
            'pendingOrders' => $pendingOrders,
        ]);
    }

    #[Route('/ajaxOrderDetails', name: '_ajax_order_details', methods: ['GET'])]
    public function orderDetails(Request $request, OrderFactory $orderFactory): JsonResponse
    {
        $order = $orderFactory->from((int)$request->query->get('order'));

        return $this->json([
            'html' => $this->renderView('warehouse/fulfillment/_order-detail.html.twig', [
                'orderId' => $order->getOrderId(),
                'orderDate' => $order->getOrderDate()->format('d/m/Y H:i'),
                'address' => $order->getCustomer()->getAddressFormatted(),
                'products' => $order->getProducts(),
            ])
        ]);
    }
}
