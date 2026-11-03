<?php

namespace App\Warehouse\Domain;

enum SupplierPromotionType: int
{
    case REDENCION = 1;
    case ADDITIONAL_DTO = 2;
    case TEMPORARY_COST_PRICE = 3;

    public function applyPromotion(float $costPrice, float $value): float
    {
        return match ($this) {
            self::ADDITIONAL_DTO => $costPrice * (1 - ($value / 100)),
            self::TEMPORARY_COST_PRICE => $value,
            default => $costPrice,
        };
    }
}
