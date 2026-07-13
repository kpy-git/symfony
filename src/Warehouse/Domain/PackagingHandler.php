<?php

namespace App\Warehouse\Domain;


use App\Warehouse\Domain\ValueObject\Package;

class PackagingHandler
{
    private float $maxWeightAllowed;

    /** @var Package[] $packages */
    public function __construct(private array $packages)
    {
        usort($this->packages, static fn(Package $a, Package $b) => $a->getMaxWeightAllowed() <=> $b->getMaxWeightAllowed());
        $this->maxWeightAllowed = empty($this->packages) ? 999 : array_last($this->packages)->getMaxWeightAllowed();
    }

    public function getCostFor(float $weight): float
    {
        $cost = 0;

        if ($weight > $this->maxWeightAllowed) {
            $cost = $this->getCostFor($weight - $this->maxWeightAllowed);
            $weight = $this->maxWeightAllowed;
        }

        foreach ($this->packages as $packaging) {
            if ($weight <= $packaging->getMaxWeightAllowed()) {
                $cost += $packaging->getCost();
                break;
            }
        }

        return $cost;
    }
}
