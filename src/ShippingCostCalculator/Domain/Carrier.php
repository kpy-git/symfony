<?php

namespace App\ShippingCostCalculator\Domain;

use App\ShippingCostCalculator\Domain\Aggregate\Range;
use App\ShippingCostCalculator\Domain\Aggregate\RangeAdditionalPerKg;
use App\ShippingCostCalculator\Domain\Exception\ShippingCostException;

class Carrier
{
    private int $id;

    private string $name;

    private string $serviceAqua;

    private float $maxShippingWeight;

    private float $maxParcelWeight;

    private bool $multiparcelAllowed;

    private array $rangesByDestination;

    private array $rangesAdditionalPerKgByDestination;


    public function __construct(int $id, string $name, string $serviceAqua, float $maxShippingWeight, bool $multiparcelAllowed = false, float $maxParcelWeight = .0 )
    {
        $this->id = $id;
        $this->name = $name;
        $this->serviceAqua = $serviceAqua;
        $this->maxShippingWeight = $maxShippingWeight;
        $this->maxParcelWeight = $maxParcelWeight;
        $this->multiparcelAllowed = $multiparcelAllowed;
        $this->rangesByDestination = [];
        $this->rangesAdditionalPerKgByDestination = [];
    }

    public function setRangesByDestination(Destination $destination, array $ranges): void
    {
        foreach ($ranges as $range) {
            if ($range instanceof Range) {
                $this->rangesByDestination[$destination->value][] = $range;
            } else {
                $this->rangesAdditionalPerKgByDestination[$destination->value] = $range;
            }
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getServiceId(): string
    {
        return $this->serviceAqua;
    }

    public function getMaxParcelWeight(): float
    {
        return $this->maxParcelWeight;
    }

    public function getMaxShippingWeight(): float
    {
        return $this->maxShippingWeight;
    }

    public function isWeightAllowed(float $weight): bool
    {
        return $weight <= $this->maxShippingWeight;
    }

    public function isMultiparcelAllowed(): bool
    {
        return $this->multiparcelAllowed;
    }

    public function getRangeAdditionalPerKgByDestination(Destination $destination): ?RangeAdditionalPerKg
    {
        if (!isset($this->rangesByDestination[$destination->value])) {
            return null;
        }

        return $this->rangesAdditionalPerKgByDestination[$destination->value] ?? null;
    }

    /**
     * @throws ShippingCostException
     */
    public function getHighestRangeCostByDestination(Destination $destination): float
    {
        if (!isset($this->rangesByDestination[$destination->value])) {
            throw new ShippingCostException(sprintf("El transportista \"%s\" no tiene ningún rango configurado para \"%s\"", $this, $destination->value));
        }

        return array_last($this->rangesByDestination[$destination->value])->getCost();
    }

    /**
     * @throws ShippingCostException
     */
    public function getCostAdditionalPerKg(Destination $destination): float
    {
        if (!isset($this->rangesAdditionalPerKgByDestination[$destination->value])) {
            throw new ShippingCostException(sprintf('El transportista "%s" no tiene tarifa adicional por kilo para "%s"', $this, $destination->value));
        }

        return $this->rangesAdditionalPerKgByDestination[$destination->value]->getCost();
    }

    /**
     * @throws ShippingCostException
     */
    public function getInitWeightAdditionalPerKg(Destination $destination): float
    {
        if (!isset($this->rangesAdditionalPerKgByDestination[$destination->value])) {
            throw new ShippingCostException(sprintf('El transportista "%s" no tiene tarifa adicional por kilo para "%s"', $this, $destination->value));
        }

        return $this->rangesAdditionalPerKgByDestination[$destination->value]->getFromWeight();
    }

    /**
     * @throws ShippingCostException
     */
    public function getRangesByDestination(Destination $destination): array
    {
        if (!isset($this->rangesByDestination[$destination->value])) {
            throw new ShippingCostException(sprintf("El transportista \"%s\" no tiene ningún rango configurado para \"%s\"", $this, $destination->value));
        }

        return $this->rangesByDestination[$destination->value];
    }

    public function hasAdditionalCostPerKgByDestination(Destination $destination): bool
    {
        return isset($this->rangesAdditionalPerKgByDestination[$destination->value]);
    }

}
