<?php

namespace App\Warehouse\Domain\ValueObject;

readonly class TrackingState
{
    public function __construct(private int $newStateId, private \DateTimeImmutable $updatedAt, private string $description)
    {
    }

    public function getNewStateId(): int
    {
        return $this->newStateId;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function __toString(): string
    {
        return sprintf("%d %s [%s]",
            $this->getNewStateId(),
            $this->getUpdatedAt()->format('d/m/Y H:i:s'),
            $this->getDescription()
        );
    }
}
