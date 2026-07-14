<?php

namespace App\Warehouse\Domain\ValueObject;

readonly class OrderCustomer
{
    public function __construct(
        private string $name,
        private string $email,
        private string $phone,
        private string $address,
        private string $city,
        private string $state,
        private string $postCode,
        private string $country,
    )
    {
    }

    public function getName(): string
    {
        return trim($this->name);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getAddressFormatted(): string
    {
        return sprintf('%s<br />%s<br />%s %s',$this->getName(), $this->address, $this->postCode, $this->city);
    }


}
