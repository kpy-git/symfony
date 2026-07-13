<?php

namespace App\Warehouse\Service;

use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;
use App\ShippingCostCalculator\Domain\Service\CalculatorShippingCost;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Domain\Warehouse;

readonly class ProductCostCalculator
{
    public function __construct(
        private readonly CalculatorShippingCost  $calculatorShippingCost,
    )
    {
    }

    /**
     * @throws ShippingCostException
     */
    public function computeCost(Product $product, Warehouse $warehouse, int $quantity = 1): float
    {
        $shippingCost = $this->calculatorShippingCost->getShippingCostBy(
            $warehouse->getCarrier(),
            $warehouse->getDefaultDestination(),
            $product->getWeight() * $quantity,
        );

        return round($warehouse->getProductCostPrice($product, $quantity) +
            $shippingCost +
            $warehouse->getPackagingHandler()->getCostFor($product->getWeight()), 6);
    }
}
