<?php

namespace App\ShippingCostCalculator\Domain\Service;

use App\Shared\Domain\Aggregate\Destination;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;

class CalculatorShippingCost
{
    /**
     * @throws ShippingCostException
     */
    public function getShippingCostBy(Carrier $carrier, Destination $destination, float $weight): float
    {
        if ($weight <= 0) {
            return .0;
        }

        if (!$carrier->isWeightAllowed($weight)) {
            throw new ShippingCostException(sprintf('El transportista "%s" no admite envíos de %.2f', $carrier, $weight));
        }

        $ranges = $carrier->getRangesByDestination($destination);

        foreach ($ranges as $range) {
            if ($range->isWeightAllowed($weight)) {
                return $range->getCost();
            }
        }

        try {
            return $carrier->getHighestRangeCostByDestination($destination) +
                ($carrier->getCostAdditionalPerKg($destination) * ceil($weight - $carrier->getInitWeightAdditionalPerKg($destination)));

        } catch (ShippingCostException) {
            throw new ShippingCostException('El transportista "%s" no tiene ningún rango configurado para %.2f kg. Revisa el peso máximo establecido o configura un rango nuevo', $carrier, $weight);
        }
    }
}
