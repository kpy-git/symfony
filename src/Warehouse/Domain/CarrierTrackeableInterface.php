<?php

namespace App\Warehouse\Domain;

interface CarrierTrackeableInterface
{
    public function getHistoryByTrackingNumberAfter(string $trackingNumber, int $timestamp): array;
}
