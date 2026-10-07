<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Exception\KpyOrderNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\API\KpyPublicApiInterface;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
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

    public function computeMarginByOrder(int $orderId): OrderMargin
    {
        $orderTotals = $this->queryBus->fetch('kpy.warehouse.query.order_margin_header', ['id_order' => $orderId]);

        if (empty($orderTotals)) {
            throw new KpyOrderNotFoundException('Order not found: ' . $orderId);
        }

        $orderMargin = new OrderMargin($orderId, $orderTotals['total_paid_tax_excl']);

        $warehouse = $this->warehouseFactory->createFrom('NEFTYS');

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

            $warehouseProduct = new Product(
                $productCode,
                $product->getBrandId(),
                $product->getWeight(),
                $productWarehouseModel->getFinalCostPrice(),
                $orderProduct['unit_sales_price'],
            );

            $marginProduct = new OrderMarginProduct(
                $productCode,
                $orderProduct['quantity'],
                $product->getWeight(),
                $warehouseProduct->getCostPrice() * $orderProduct['quantity']
            );

            $productsCost += $warehouseProduct->getCostPrice();

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

        /** @var OrderMarginProduct $orderProduct */
        foreach ($orderMargin->getProducts() as $orderProduct) {
            $prorated = $orderProduct->getWeight() / $orderWeight;
            $orderProduct
                ->setShippingCost($shippingCost * $prorated);

            if (!$warehouse->hasManipulationCostPerProduct()) {
                $orderProduct
                    ->setPackingCost($packingCost * $prorated)
                    ->setManipulationCost($orderManipulationCost * $prorated);
            }
        }

        $orderMargin->setTotalProductsCost($productsCost);
        $orderMargin->setTotalManipulationCost($orderManipulationCost);
        $orderMargin->setTotalShippingCost($shippingCost);
        $orderMargin->setTotalPackingCost($packingCost);

        return $orderMargin;
    }
}
