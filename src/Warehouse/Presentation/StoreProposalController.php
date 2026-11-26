<?php

namespace App\Warehouse\Presentation;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\JsonResponseGenerator;
use App\Shared\Domain\Service\Policy\Context\RequestPolicyContext;
use App\Shared\Domain\Service\Policy\MandatoryParamsRequestPolicy;
use App\Shared\Domain\Service\Policy\RequestPolicy;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Domain\ValueObject\ProposalProduct;
use App\Warehouse\Infrastructure\Persistence\Repository\SupplierRepository;
use App\Warehouse\Service\StoreProposalProvider;
use App\Warehouse\Service\SupplierOrderHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route('/buyproposal', name: 'warehouse_proposal_', host: 'app.%kpy.base_domain%')]
class StoreProposalController extends AbstractController
{
    public function __construct(
        private readonly StoreProposalProvider $storeProposalProvider,
        private readonly JsonResponseGenerator $jsonResponseGenerator)
    {
    }

    #[Route(name: 'index')]
    public function index(): Response
    {
        return $this->render('warehouse/proposal/index.html.twig');
    }

    #[Route('/suppliersPreview', name: 'suppliers_preview')]
    public function suppliersPreview(): JsonResponse
    {
        try {
            $suppliersPreview = $this->storeProposalProvider->getSuppliersPreview();

            return $this->jsonResponseGenerator->success([
                'suppliers' => $suppliersPreview,
                'html' => $this->renderView('warehouse/proposal/_suppliers_list.html.twig', [
                    'suppliers' => $suppliersPreview,
                ])
            ]);

        } catch (KpyException $kpyException) {
            return $this->jsonResponseGenerator->error($kpyException->getMessage());
        }
    }

    #[Route('/supplier/{supplierCode}', name: 'supplier', requirements: ['supplierCode' => '4[0-9]{8}'], methods: 'GET')]
    public function supplier(string $supplierCode, SupplierRepository $supplierRepository): Response
    {
        try {
            $products = $this->storeProposalProvider->getSupplierProducts($supplierCode);
            $totals = [
                'taxInc' => 0.0,
                'taxExcl' => 0.0,
            ];
            /** @var ProposalProduct $product */
            foreach ($products as $product) {
                $totals['taxInc'] += $product->getTotalPriceTaxInc();
                $totals['taxExcl'] += $product->getTotalPriceTaxExcl();
            }

            return $this->render('warehouse/proposal/supplier.html.twig', [
                'supplier' => $supplierRepository->findByNifOrFail($supplierCode),
                'products' => $products,
                'orderTotal' => $totals,
            ]);

        } catch (KpyException $kpyException) {
            return $this->jsonResponseGenerator->error($kpyException->getMessage());
        }
    }

    #[Route('/createOrder', name: 'create_order', methods: ['POST'])]
    public function createOrder(
        Request                      $request,
        MandatoryParamsRequestPolicy $policy,
        SupplierOrderHandler         $supplierOrderHandler,
        DenormalizerInterface        $denormalizer,
        CommandBus                   $commandBus,
    ): JsonResponse
    {
        try {
            if ($request->getContentTypeFormat() !== 'json') {
                return $this->jsonResponseGenerator->error('Format not supported', Response::HTTP_BAD_REQUEST);
            }

            $policy->apply(new RequestPolicyContext($request, [
                RequestPolicy::MANDATORY_PARAMS => ['supplier', 'products']
            ]));

            $inputBag = $request->getPayload()->all();

            $products = $denormalizer->denormalize(
                $inputBag['products'],
                ProposalProduct::class . '[]',
                null, [
                // habilita la conversión automática de floats, int y bool
                AbstractObjectNormalizer::ENABLE_TYPE_CONVERSION => true,
            ]);

            $order = $supplierOrderHandler->createOrder($inputBag['supplier'], $products);

            $supplierOrderHandler->save($order);

            /** @var ProposalProduct $product */
            foreach ($products as $product) {
                $commandBus->execute('kpy.warehouse.command.prebuy_product_remove_quantity', [
                    'sku' => $product->getProductCode()->getSku(),
                    'quantity' => $product->getUnits(),
                ]);
            }

            return $this->jsonResponseGenerator->success([
                'order_number' => $order->getNumeroDocumento(),
            ]);

        } catch (KpyException $exception) {
            return $this->jsonResponseGenerator->fromException($exception);
        }
    }
}
