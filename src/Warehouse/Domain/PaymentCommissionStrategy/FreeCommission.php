<?php

namespace App\Warehouse\Domain\PaymentCommissionStrategy;

class FreeCommission implements PaymentCommissionStrategyInterface
{

    public function calculateCommission(float $totalPaid): float
    {
        return 0;
    }
}
