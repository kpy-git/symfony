<?php

namespace App\Warehouse\Domain\Carrier;

use App\Warehouse\Domain\ValueObject\OrderCustomer;

abstract class AbstractRecipient
{
    protected string $name = '';

    protected string $nif = '';

    protected string $address = '';

    protected string $city = '';

    protected string $postcode = '';

    protected string $phone = '';

    protected string $email = '';

    protected string $countryISO = 'ES';

    protected string $state = '';

    public function fillWith(OrderCustomer $customer): void
    {
        $this->name = $customer->getName();
        $this->address = $customer->getAddress();
        $this->city = $customer->getCity();
        $this->postcode = $customer->getPostcode();
        $this->phone = $customer->getPhone();
        $this->email = $customer->getEmail();
        $this->state = $customer->getState();
    }

    abstract public function normalize(): array;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getNif(): string
    {
        return $this->nif;
    }

    public function setNif(string $nif): static
    {
        $this->nif = $nif;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): static
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getCountryISO(): string
    {
        return $this->countryISO;
    }

    public function setCountryISO(string $countryISO): static
    {
        $this->countryISO = $countryISO;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): AbstractRecipient
    {
        $this->state = $state;
        return $this;
    }
}
