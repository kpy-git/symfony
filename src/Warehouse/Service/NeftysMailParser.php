<?php

namespace App\Warehouse\Service;

use App\Warehouse\Domain\ValueObject\ExchangeMail;

class NeftysMailParser
{
    private int $orderId;

    private string $trackingNumber;

    public function parserMail(ExchangeMail $mail): void
    {
        $this->orderId = 0;
        $this->trackingNumber = '';

        $pattern = "/pedido\s+(\d+)\s+con el número de envío\s+([A-Za-z0-9]+)/iu";

        if (preg_match($pattern, $mail->getBodyPreview(), $matches)) {
            $this->orderId = $matches[1];
            $this->trackingNumber  = $matches[2];
        }
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }
}
