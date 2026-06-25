<?php

namespace App\Shared\Domain;

enum Country: string
{
    CASE SPAIN = "ESPAÑA";
    CASE PORTUGAL = "PORTUGAL";

    public function getISO(): string
    {
        return match ($this) {
            self::SPAIN => "ES",
            self::PORTUGAL => "PT",
        };
    }

    public function getPrestaShopId(): int
    {
        return match ($this) {
            self::SPAIN => 6,
            self::PORTUGAL => 15,
        };
    }
}
