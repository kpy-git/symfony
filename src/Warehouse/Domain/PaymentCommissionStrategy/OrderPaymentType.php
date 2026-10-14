<?php

namespace App\Warehouse\Domain\PaymentCommissionStrategy;

enum OrderPaymentType: string
{
    case TPV = 'TPV';
    case PAYPAL = 'PAYPAL';
    case CRM = 'CRM';
    case BANKWIRE = 'BANKWIRE';
    case FREE_ORDER = 'FREE_ORDER';

    public static function createFromModuleName(string $module): self
    {
        return match ($module) {
            'redsyspur' => self::TPV,
            'ps_checkout' => self::PAYPAL,
            'kpycashondelivery' => self::CRM,
            'ps_wirepayment' => self::BANKWIRE,
            'free_order' => self::FREE_ORDER,
        };
    }

    public function getCommissionStrategy(): PaymentCommissionStrategyInterface
    {
        return match ($this) {
            self::TPV => new RedsysCommission(),
            self::PAYPAL => new PayPalCommission(),
            default => new FreeCommission(),
        };
    }
}
