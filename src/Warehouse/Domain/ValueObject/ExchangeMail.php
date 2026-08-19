<?php

namespace App\Warehouse\Domain\ValueObject;

readonly class ExchangeMail
{
    public function __construct(
        private string             $id,
        private string             $subject,
        private string             $bodyPreview,
        private \DateTimeImmutable $receivedDateTime,
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBodyPreview(): string
    {
        return $this->bodyPreview;
    }

    public function getReceivedDateTime(): \DateTimeImmutable
    {
        return $this->receivedDateTime;
    }

}
