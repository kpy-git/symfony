<?php

namespace App\Warehouse\Service;

use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Domain\ValueObject\ProductFulfillmentCost;
use App\Warehouse\Domain\Warehouse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class FulfillmentProductCostCalculator
{
    public function __construct(
        private CalculatorShippingCost $calculatorShippingCost,
        #[Autowire('%env(float:THRESHOLD_FOR_INCLUDING_SHIPPING_COST_IN_FULFILLMENT_PRICE)%')]
        private float $thresholdForIncludingShippingCost,
    )
    {
    }

    /**
     * @throws ShippingCostException
     */
    public function computeFulfillmentCostByProduct(
        Product $product,
        Warehouse $warehouse,
        int $quantity = 1): ProductFulfillmentCost
    {
        $shippingCost = $product->getSalesPrice() < $this->thresholdForIncludingShippingCost ? 0 :
            $this->calculatorShippingCost->getShippingCostBy(
            $warehouse->getCarrier(),
            $warehouse->getDefaultDestination(),
            $product->getWeight() * $quantity,
        );

        return new ProductFulfillmentCost(
            $product->getProductCode(),
            $product->getCostPrice(),
            $warehouse->getManipulationCost($product, $quantity),
            $shippingCost,
            $warehouse->getPackagingHandler()->getCostFor($product->getWeight()));
    }

    /**
     * @return ProductFulfillmentCost[]
     * @throws ShippingCostException
     */
    public function computeFulfillmentCostByWarehouse(Warehouse $warehouse): array
    {
        return array_map(
            fn (Product $product): ProductFulfillmentCost => $this->computeFulfillmentCostByProduct($product, $warehouse),
            $warehouse->getAllProducts()
        );
    }
}
