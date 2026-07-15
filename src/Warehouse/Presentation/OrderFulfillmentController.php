<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Infrastructure\Database\DatabaseInterface;
use App\Warehouse\Domain\OrderFactory;
use App\Warehouse\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

//#[IsGranted("ROLE_WAREHOUSE")]
#[Route(host: 'warehouse.%kpy.base_domain%', name: 'warehouse_')]
final class OrderFulfillmentController extends AbstractController
{
    public function __construct(
        private readonly QueryBus              $queryBus,
        private readonly JsonResponseGenerator $jsonResponseGenerator
    )
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

        return $this->jsonResponseGenerator->success([
            'html' => $this->renderView('warehouse/fulfillment/_order-detail.html.twig', [
                'orderId' => $order->getOrderId(),
                'orderDate' => $order->getOrderDate()->format('d/m/Y H:i'),
                'address' => $order->getCustomer()->getAddressFormatted(),
                'products' => $order->getProducts(),
            ])
        ]);
    }

    #[Route('/ajaxCreateShipment', name: '_ajax_create_shipment', methods: ['POST'])]
    public function createShipment(Request $request, OrderFactory $orderFactory): JsonResponse
    {
        return $this->jsonResponseGenerator->success();
    }

    #[Route('/ajaxGetLabel', name: '_ajax_get_label', methods: ['GET'])]
    public function getLabel(Request $request, DatabaseInterface $aquaDatabase): JsonResponse
    {
        $order = $request->query->get('order');

        return $this->jsonResponseGenerator->success([
            'label' => $aquaDatabase->getValue("SELECT ETIQUETA FROM DATPYMETIQUETAS01 WITH(NOLOCK) WHERE PEDIDO='$order'")
        ]);
    }

    #[Route('/sing-print', name: '_sign_print', methods: ['POST'])]
    public function signPrint(
        Request $request,
        #[Autowire('%env(resolve:QZ_PRIVATE_KEY_PATH)%')]
        string $privateKeyPath
    ): Response
    {
        // 1. Obtener el 'toSign' que envía QZ Tray en el cuerpo de la petición
        $toSign = $request->getContent();

        if (empty($toSign)) {
            return new Response('No content to sign', Response::HTTP_BAD_REQUEST);
        }

        // 2. Verificar que la clave privada existe
        if (!file_exists($privateKeyPath)) {
            return new Response('Private key file not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 3. Leer la clave privada
        $privateKeyContent = file_get_contents($privateKeyPath);
        $privateKey = openssl_pkey_get_private($privateKeyContent);

        if (!$privateKey) {
            return new Response('Invalid private key format', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 4. Firmar el contenido con SHA512
        $signature = '';
        $success = openssl_sign($toSign, $signature, $privateKey, OPENSSL_ALGO_SHA512);

        if (!$success) {
            return new Response('Signing failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 5. Retornar la firma codificada en Base64 con tipo de contenido text/plain
        return new Response(
            base64_encode($signature),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }
}
