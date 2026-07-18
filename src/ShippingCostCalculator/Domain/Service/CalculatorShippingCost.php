<?php

namespace App\ShippingCostCalculator\Domain\Service;

use App\Shared\Domain\Destination;
use App\ShippingCostCalculator\Domain\Aggregate\Range;
use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;

class CalculatorShippingCost
{
    private ?Carrier $carrier;
    private ?Destination $destination;

    public function __construct()
    {
        $this->carrier = null;
        $this->destination = null;
    }

    /**
     * @throws ShippingCostException
     */
    public function getShippingCostBy(Carrier $carrier, Destination $destination, float $weight): float
    {
        if ($weight < 0) {
            return .0;
        }

        if (!$carrier->isWeightAllowed($weight)) {
            throw new ShippingCostException(sprintf('El transportista "%s" no admite envíos de %.2f', $carrier, $weight));
        }

        $ranges = $carrier->getRangesByDestination($destination);

        /** @var Range $range */
        foreach ($ranges as $range) {
            if ($range->isWeightAllowed($weight)) {
                return $range->getCost();
            }
        }

        try {
            return $carrier->getHighestRangeCostByDestination($destination) +
                ($carrier->getCostAdditionalPerKg($destination) * ceil($weight - $carrier->getInitWeightAdditionalPerKg($destination)));

        } catch (ShippingCostException) {
            throw new ShippingCostException(sprintf('El transportista "%s" no tiene ningún rango configurado para %f kg. Revisa el peso máximo establecido o configura un rango nuevo', $carrier, $weight));
        }
    }

    public function setFixedCarrierAnDestination(Carrier $carrier, Destination $destination): void
    {
        $this->carrier = $carrier;
        $this->destination = $destination;
    }

    /**
     * @throws ShippingCostException
     */
    public function calculateShippingCostByWeightWithSavedConfiguration(float $weight): float
    {
        if ($this->carrier === null) {
            throw new ShippingCostException('No se puede calcular el coste del transporte, no hay ningún transportista configurado. ¿se te ha olvidado llamar al método CalculatorShippingCost::setFixedCarrierAnDestination?');
        }

        if ($this->destination === null) {
            throw new ShippingCostException('No se puede calcular el coste del transporte, no hay ningún destino configurado. ¿se te ha olvidado llamar al método CalculatorShippingCost::setFixedCarrierAnDestination?');
        }

        return $this->getShippingCostBy($this->carrier, $this->destination, $weight);
    }
}
