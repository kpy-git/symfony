<?php

namespace App\Warehouse\Domain\ValueObject;

class SupplierPreview
{
    private float $total;

    private float $minAmount;

    private int $lines;

    private ?string $cutoffTime;

    public function __construct(private readonly string $code, private readonly string $name)
    {
        $this->total = 0;
        $this->minAmount = 0;
        $this->lines = 0;
        $this->cutoffTime = null;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getMinAmount(): float
    {
        return $this->minAmount;
    }

    public function setMinAmount(float $minAmount): static
    {
        $this->minAmount = $minAmount;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCssClass(): string
    {
        return $this->total > $this->minAmount ? "success" : "warning";
    }

    public function getLines(): int
    {
        return $this->lines;
    }

    public function setLines(int $lines): static
    {
        $this->lines = $lines;
        return $this;
    }

    public function getCutoffTime(): string
    {
        return $this->cutoffTime ?? '';
    }

    public function setCutoffTime(string $cutoffTime): static
    {
        $this->cutoffTime = $cutoffTime;
        return $this;
    }

}
