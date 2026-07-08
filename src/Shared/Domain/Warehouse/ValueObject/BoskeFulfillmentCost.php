<?php

namespace App\Shared\Domain\Warehouse\ValueObject;

readonly class BoskeFulfillmentCost
{
    public function __construct(
        private float $singleItemUpTo5Kg,
        private float $singleItemStartingAt5Kg,
        private float $additionalItemsUpTo5Kg,
        private float $additionalItemsStartingAt5Kg,
    )
    {
    }

    public function getSingleItemUpTo5Kg(): float
    {
        return $this->singleItemUpTo5Kg;
    }

    public function getSingleItemStartingAt5Kg(): float
    {
        return $this->singleItemStartingAt5Kg;
    }

    public function getAdditionalItemsUpTo5Kg(): float
    {
        return $this->additionalItemsUpTo5Kg;
    }

    public function getAdditionalItemsStartingAt5Kg(): float
    {
        return $this->additionalItemsStartingAt5Kg;
    }

}
