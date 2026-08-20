<?php

namespace App\Warehouse\Domain\ValueObject;

class OrderState
{
    private int $orderId;

    private string $trackingNumber;

    private int $currentState;

    private \DateTimeImmutable $updateAt;

    public function __construct(int $orderId, string $trackingNumber, int $currentState, \DateTimeImmutable $updateAt)
    {
        $this->orderId = $orderId;
        $this->trackingNumber = $trackingNumber;
        $this->currentState = $currentState;
        $this->updateAt = $updateAt;
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

    public function setUpdateAt(\DateTimeImmutable $updateAt): OrderState
    {
        $this->updateAt = $updateAt;
        return $this;
    }

}
