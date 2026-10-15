<?php

namespace App\Warehouse\Service;

use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\Exception\KpyOrderNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\API\KpyPublicApiInterface;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use App\Warehouse\Domain\Exception\WarehouseNotFoundException;
use App\Warehouse\Domain\PaymentCommissionStrategy\OrderPaymentType;
use App\Warehouse\Domain\ValueObject\OrderMargin;
use App\Warehouse\Domain\ValueObject\OrderMarginProduct;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use App\Warehouse\Query\QueryBus;

readonly class OrderMarginCalculator
{
    public function __construct(
        private WarehouseFactory $warehouseFactory,
        private CalculatorShippingCost  $calculatorShippingCost,
        private QueryBus $queryBus,
        private KpyPublicApiInterface $kpyPublicApi,
        private WarehouseProductRepository $warehouseProductRepository
    )
    {
    }

    /**
     * @throws KpyOrderNotFoundException
     * @throws KpyInvalidProductCode
     * @throws WarehouseNotFoundException
     * @throws KpyQueryNotFoundException
     * @throws ShippingCostException
     */
    public function computeMarginByOrder(int $orderId): OrderMargin
    {
        $orderHeader = $this->queryBus->fetch('kpy.warehouse.query.order_margin_header', ['id_order' => $orderId]);

        if (empty($orderHeader)) {
            throw new KpyOrderNotFoundException('Order not found: ' . $orderId);
        }

        $orderMargin = new OrderMargin($orderId, $orderHeader['total_paid_tax_excl']);

        $warehouse = $this->warehouseFactory->createFrom($orderHeader['warehouse']);

        $carrier = $warehouse->getCarrier();

        $orderWeight = 0;
        $orderManipulationCost = 0;
        $productsCost = 0;

        $orderProducts = $this->queryBus->fetch('kpy.warehouse.query.order_margin_products', ['id_order' => $orderId]);

        foreach ($orderProducts as $orderProduct) {
            $productCode = ProductCode::fromSKU($orderProduct['sku']);

            $product = $this->kpyPublicApi->getProduct($productCode);

            $orderWeight += $product->getWeight() * $orderProduct['quantity'];

            $productWarehouseModel = $this->warehouseProductRepository->findProductInWarehouse($productCode, $warehouse);

            if (!$productWarehouseModel) {
                $productWarehouseModel = $this->warehouseProductRepository->findProductInWarehouse($productCode, $this->warehouseFactory->createFrom('TIENDA'));
            }

            $warehouseProduct = new Product(
                $productCode,
                $product->getBrandId(),
                $product->getWeight(),
                $productWarehouseModel->getFinalCostPrice()
            );

            $marginProduct = new OrderMarginProduct(
                $productCode,
                (int)$orderProduct['NUMERO'],
                (int)$orderProduct['POSICION'],
                $orderProduct['quantity'],
                $product->getWeight() * $orderProduct['quantity'],
                $warehouseProduct->getCostPrice() * $orderProduct['quantity']
            );

            $productsCost += $warehouseProduct->getCostPrice() * $orderProduct['quantity'];

            // solo calcula el coste de manipulación la primera vez para los almacenes que tienen coste fijo por pedido
            if ($warehouse->hasManipulationCostPerProduct() || $orderManipulationCost === 0) {
                $manipulationCost = $warehouse->getManipulationCost(
                    $warehouseProduct,
                    $orderProduct['quantity']
                );

                $marginProduct->setManipulationCost($manipulationCost);
                $orderManipulationCost += $manipulationCost;
            }

            $orderMargin->addProduct($marginProduct);
        }

        $shippingCost = $this->calculatorShippingCost->getShippingCostBy(
            $carrier,
            $warehouse->getDefaultDestination(),
            $orderWeight
        );

        $packingCost = $warehouse->getPackagingHandler()->getCostFor($orderWeight);

        $paymentCommission = OrderPaymentType::createFromModuleName($orderHeader['module'])
            ->getCommissionStrategy()
            ->calculateCommission($orderHeader['total_paid']);

        /** @var OrderMarginProduct $orderProduct */
        $orderProducts = $orderMargin->getProducts();

        foreach ($orderProducts as $orderProduct) {
            $prorated = $orderProduct->getWeight() / $orderWeight;
            $orderProduct
                ->setShippingCost($shippingCost * $prorated)
                ->setPackingCost($packingCost * $prorated)
                ->setPaymentCommission($paymentCommission / count($orderProducts));

            if (!$warehouse->hasManipulationCostPerProduct()) {
                $orderProduct
                    ->setManipulationCost($orderManipulationCost / count($orderProducts));
            }
        }

        $orderMargin->setTotalProductsCost($productsCost);
        $orderMargin->setTotalManipulationCost($orderManipulationCost);
        $orderMargin->setTotalShippingCost($shippingCost);
        $orderMargin->setTotalPackingCost($packingCost);
        $orderMargin->setPaymentCommission($paymentCommission);

        return $orderMargin;
    }
}
