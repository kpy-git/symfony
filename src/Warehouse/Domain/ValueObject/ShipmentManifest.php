<?php

namespace App\Warehouse\Domain\ValueObject;

class ShipmentManifest
{
    private string $number;
    private string $reference;
    private \DateTimeImmutable $collectionDate;
    private string $recipientName;
    private string $postCode;
    private string $city;
    private string $province;
    private int $packagesCount;
    private int $weight;
    private float $codAmount = 0.0;

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCollectionDate(): \DateTimeImmutable
    {
        return $this->collectionDate;
    }

    public function setCollectionDate(\DateTimeImmutable $collectionDate): self
    {
        $this->collectionDate = $collectionDate;
        return $this;
    }

    public function getRecipientName(): string
    {
        return $this->recipientName;
    }

    public function setRecipientName(string $recipientName): self
    {
        $this->recipientName = $recipientName;
        return $this;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): self
    {
        $this->postCode = $postCode;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;
        return $this;
    }

    public function getPackagesCount(): int
    {
        return $this->packagesCount;
    }

    public function setPackagesCount(int $packagesCount): self
    {
        $this->packagesCount = $packagesCount;
        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getCodAmount(): float
    {
        return $this->codAmount;
    }

    public function setCodAmount(float $codAmount): self
    {
        $this->codAmount = $codAmount;
        return $this;
    }

}
