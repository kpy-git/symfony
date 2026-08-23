<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Service\OrderReadyToShipUpdater;
use App\Warehouse\Application\ShipmentGenerator;
use App\Warehouse\Domain\OrderFactory;
use App\Warehouse\Domain\OrderTrackerUpdater;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\ShipmentEntity;
use App\Warehouse\Infrastructure\Persistence\PrinterConfigRepository;
use App\Warehouse\Query\QueryBus;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(PrinterConfigRepository $printerConfigRepository): Response
    {
        // todo - el estado y la configuración de la impresora hay que sacarlos en función del role del usuario
        $pendingOrders = $this->queryBus->fetch('kpy.warehouse.query.pending_orders_kompychinales', [
            'state' => (int)$_ENV['OWNERSHIP_WAREHOUSE_OS'],
        ]);

        return $this->render('warehouse/fulfillment/index.html.twig', [
            'pendingOrders' => $pendingOrders,
            'printer_config' => $printerConfigRepository->getConfig('kompy')
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
    public function createShipment(
        Request $request,
        OrderFactory $orderFactory,
        ShipmentGenerator $shipmentGenerator,
        EntityManagerInterface $entityManager,
        OrderReadyToShipUpdater $orderReadyToShipUpdater,
    ): JsonResponse
    {
        try {
            $orderId = (int)$request->request->get('order');
            $parcels = (int)$request->request->get('parcels');

            $order = $orderFactory->from($orderId);

            $shipment = $shipmentGenerator->generateShipment($order, $parcels);

            $entity = new ShipmentEntity();
            $entity
                ->setOrderId($order->getOrderId())
                ->setTrackingNumber($shipment->getTrackingNumber())
                ->setLabel($shipment->getZpl());

            $entityManager->persist($entity);
            $entityManager->flush();

            $orderReadyToShipUpdater->updateOrder($orderId, $shipment->getTrackingNumber());

            return $this->jsonResponseGenerator->success([
                'label' => $shipment->getZpl()
            ]);

        } catch (KpyException $kpyException) {
            return $this->jsonResponseGenerator->error($kpyException->getMessage());
        }
    }

    #[Route('/ajaxGetLabel', name: '_ajax_get_label', methods: ['GET'])]
    public function getLabel(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $request->query->get('order');
        $shipmentEntity = $entityManager->getRepository(ShipmentEntity::class)->findBy(['orderId' => $order]);

        return $this->jsonResponseGenerator->success([
            'label' => $shipmentEntity->getLabel(),
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

    #[Route('/tracking/{tracking}', name: '_tracking', methods: ['GET'])]
    public function trackingTest(string $tracking, OrderTrackerUpdater $orderTrackerUpdater): JsonResponse
    {
        return $this->jsonResponseGenerator->success([
            'tracking' => $orderTrackerUpdater->getHistoryByTrackingNumber($tracking),
        ]);
    }

    #[Route('/tracking/order/{order}', name: '_order_tracking', methods: ['GET'])]
    public function trackingOrderTest(int $order, OrderTrackerUpdater $orderTrackerUpdater): JsonResponse
    {
        $orderTrackerUpdater->updateHistoryByOrder($order);

        return $this->jsonResponseGenerator->success();
    }
}
