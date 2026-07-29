<?php

namespace App\Warehouse\Domain\ValueObject;

readonly class Shipment
{
    public function __construct(
        private int $orderId,
        private string $zpl,
        private string $trackingNumber,
    )
    {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getZpl(): string
    {
        return $this->zpl;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

}
