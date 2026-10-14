<?php

namespace App\Warehouse\Domain\PaymentCommissionStrategy;

class RedsysCommission implements PaymentCommissionStrategyInterface
{

    public function calculateCommission(float $totalPaid): float
    {
        return $totalPaid > 0 ? round($totalPaid * 0.003, 2) : 0;
    }
}
