<?php

namespace App\Warehouse\Domain\PaymentCommissionStrategy;

class PayPalCommission implements PaymentCommissionStrategyInterface
{

    public function calculateCommission(float $totalPaid): float
    {
        return $totalPaid > 0 ? round($totalPaid * 0.029 + 0.35, 2) : 0;
    }
}
