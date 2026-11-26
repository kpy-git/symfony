<?php

namespace App\Shared\Domain\ValueObject;

enum Tax: int
{
    case _10 = 3;
    case _21 = 2;
    case _4 = 1;

    public function applyTaxToAmount(float $amount, int $decimals = 2): float
    {
        return (float)bcmul((string)$amount, (string)$this->getTaxRate(), $decimals);
    }

    public function getTaxesForAmount(float $amount, int $decimals = 2): float
    {
        return (float)bcmul((string)$amount, (string)($this->getTaxRate() - 1), $decimals);
    }

    public function getTaxRate(): float
    {
        return match ($this) {
            self::_10 => 1.1,
            self::_21 => 1.21,
            default => 1.04
        };
    }

    public function getAquaType(): int
    {
        return match ($this) {
            self::_10 => 3,
            self::_21 => 2,
            self::_4 => 1,
        };
    }
}
