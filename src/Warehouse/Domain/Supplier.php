<?php

namespace App\Warehouse\Domain;

class Supplier
{
    private string $code;

    private string $name;

    private float $minPurchaseAmount;

    private ?string $cutoffTime;

    private string $formaPago;

    private string $nEfecto;

    private string $nCuenta;

    private array $descuentos = [];

    private float $descuentoEnCascadaAcumulado = 1.0;

    private bool $exentoIVA;

    private int $fulfillmentDays = 1;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Supplier
    {
        $this->name = $name;
        return $this;
    }

    public function getMinPurchaseAmount(): float
    {
        return $this->minPurchaseAmount;
    }

    public function setMinPurchaseAmount(float $minPurchaseAmount): Supplier
    {
        $this->minPurchaseAmount = $minPurchaseAmount;
        return $this;
    }

    public function getCutoffTime(): ?string
    {
        return $this->cutoffTime;
    }

    public function setCutoffTime(?string $cutoffTime): Supplier
    {
        $this->cutoffTime = $cutoffTime;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): Supplier
    {
        $this->code = $code;
        return $this;
    }

    public function getFormaPago(): string
    {
        return $this->formaPago;
    }

    public function setFormaPago(string $formaPago): Supplier
    {
        $this->formaPago = $formaPago;
        return $this;
    }

    public function getNEfecto(): string
    {
        return $this->nEfecto;
    }

    public function setNEfecto(string $nEfecto): Supplier
    {
        $this->nEfecto = $nEfecto;
        return $this;
    }

    public function getNCuenta(): string
    {
        return $this->nCuenta;
    }

    public function setNCuenta(string $nCuenta): Supplier
    {
        $this->nCuenta = $nCuenta;
        return $this;
    }

    public function getDescuentos(): array
    {
        return $this->descuentos;
    }

    public function setDescuentos(array $descuentos): Supplier
    {
        $this->descuentos = $descuentos;
        foreach ($descuentos as $descuento) {
            $this->descuentoEnCascadaAcumulado *= 1 - ($descuento / 100);
        }

        return $this;
    }

    public function getDescuentoEnCascadaAcumulado(): float
    {
        return $this->descuentoEnCascadaAcumulado;
    }

    public function isExentoIVA(): bool
    {
        return $this->exentoIVA;
    }

    public function setExentoIVA(bool $exentoIVA): Supplier
    {
        $this->exentoIVA = $exentoIVA;
        return $this;
    }

    public function getFulfillmentDays(): int
    {
        return $this->fulfillmentDays;
    }

    public function setFulfillmentDays(int $fulfillmentDays): Supplier
    {
        $this->fulfillmentDays = $fulfillmentDays;
        return $this;
    }

}
