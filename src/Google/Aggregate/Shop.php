<?php

namespace App\Google\Aggregate;

use App\Shared\Domain\Aggregate\Destination;

enum Shop: string
{
    case KOMPY_ES = 'KOMPY_ES';

    public function getShopId(): int
    {
        return match ($this) {
            self::KOMPY_ES => 1,
        };
    }

    public function getLanguageId(): int
    {
        return match ($this) {
            self::KOMPY_ES => 1,
        };
    }

    public function priceLimitToShippingFree(): float
    {
        return match ($this) {
            self::KOMPY_ES => 39.90,
        };
    }

    public function getLimitToCalculateShippingCost(): float
    {
        return match ($this) {
            self::KOMPY_ES => 32,
        };
    }

    public function shippingPriceByDestination(Destination $destination): float
    {
        $shippingsPrice = [
            self::KOMPY_ES->value => [
                Destination::PENINSULA->value => 5.99,
                Destination::CORDOBA->value => 5.99,
                Destination::BALEARES->value => 9.99,
            ]
        ];

        return $shippingsPrice[self::KOMPY_ES->value][$destination->value] ?? 0;
    }

    public function getCountryISO(): string
    {
        return match ($this) {
            self::KOMPY_ES => 'ES',
        };
    }

    public function getDomain(): string
    {
        return match ($this) {
            self::KOMPY_ES => 'kompymascotas.com',
        };
    }
}
