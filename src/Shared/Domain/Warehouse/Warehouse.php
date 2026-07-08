<?php

namespace App\Shared\Domain\Warehouse;

use App\Shared\Domain\Destination;
use App\Shared\Domain\Warehouse\CostStrategy\CostStrategyInterface;
use App\Shared\Domain\Warehouse\ValueObject\BoskeFulfillmentCost;
use App\Shared\Domain\Warehouse\ValueObject\Product;
use App\ShippingCostCalculator\Domain\Carrier;

class Warehouse
{
    protected ?Carrier $carrier = null;

    public function __construct(
        private readonly CostStrategyInterface $costStrategy,
        private readonly BoskeFulfillmentCost $boskeFulfillmentCost
    )
    {
    }

    public function getFinalCostPrice(Product $product, int $quantity = 1): float
    {
        if ($product->isBoske()) {
            return $this->computeBoskeCost($quantity, $product->getWeight());
        }

        return $this->costStrategy->computeFinalCostPrice($product, $quantity);
    }

    public function getCarrier(): ?Carrier
    {
        return $this->carrier;
    }

    public function getDefaultDestination(): Destination
    {
        return Destination::PENINSULA;
    }

    public function computeBoskeCost(int $quantity, float $weight): float
    {
        $singleItemCost = match(true) {
            $weight < 5 => $this->boskeFulfillmentCost->getSingleItemUpTo5Kg(),
            default => $this->boskeFulfillmentCost->getSingleItemStartingAt5Kg()
        };

        $additionalUnitsCost = match(true) {
            $weight < 5 => $this->boskeFulfillmentCost->getAdditionalItemsUpTo5Kg(),
            default => $this->boskeFulfillmentCost->getAdditionalItemsStartingAt5Kg()
        };

        return round($singleItemCost + (($quantity - 1) * $additionalUnitsCost), 6);
    }

}
