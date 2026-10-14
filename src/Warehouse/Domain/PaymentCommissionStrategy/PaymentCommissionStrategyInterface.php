<?php

namespace App\Warehouse\Domain\PaymentCommissionStrategy;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.payment_commission_strategy')]
interface PaymentCommissionStrategyInterface
{
    public function calculateCommission(float $totalPaid): float;
}
