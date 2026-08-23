<?php

namespace App\Warehouse\Domain\ValueObject;

class OrderState
{

    public function __construct(
        private readonly int                $orderId,
        private readonly string             $trackingNumber,
        private int                         $currentState,
        private readonly \DateTimeImmutable $updateAt,
        private bool                        $shipped,
        private readonly string             $warehouse
    )
    {
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function getCurrentState(): int
    {
        return $this->currentState;
    }

    public function getUpdateAt(): \DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setCurrentState(int $currentState): OrderState
    {
        $this->currentState = $currentState;
        return $this;
    }

    public function shipped(): void
    {
        $this->shipped = true;
    }

    public function isShipped(): bool
    {
        return $this->shipped;
    }

    /**
     * @return string
     */
    public function getWarehouse(): string
    {
        return $this->warehouse;
    }
}
