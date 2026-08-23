<?php

namespace App\Warehouse\Domain\ValueObject;

class TrackingUpdateResult
{
    private array $history;

    public function __construct(
        private readonly int $orderId,
        private readonly int $lastStateId,
        private readonly \DateTimeImmutable $lastUpdate,
    )
    {
        $this->history = [];
    }

    public function addState(TrackingState $stateResult): void
    {
        $this->history[] = $stateResult;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getLastStateId(): int
    {
        return $this->lastStateId;
    }

    public function getLastUpdate(): \DateTimeImmutable
    {
        return $this->lastUpdate;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function withStatusChanges(): bool
    {
        return !empty($this->history);
    }
}
