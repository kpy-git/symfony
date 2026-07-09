<?php

namespace App\Warehouse\Domain;

use App\Shared\Domain\Destination;
use App\ShippingCostCalculator\Domain\Carrier;
use App\Warehouse\Domain\CostStrategy\CostStrategyInterface;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\BoskeFulfillmentCost;
use App\Warehouse\ValueObject\Product;

class Warehouse
{
    protected ?Carrier $carrier = null;

    public function __construct(
        private readonly CostStrategyInterface $costStrategy,
        private readonly BoskeFulfillmentCost $boskeFulfillmentCost,
        private readonly bool $packagingCostIncluded = false
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

    public function isPackagingCostIncluded(): bool
    {
        return $this->packagingCostIncluded;
    }

}
