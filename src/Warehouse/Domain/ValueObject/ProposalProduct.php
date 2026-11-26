<?php

namespace App\Warehouse\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Domain\ValueObject\Tax;
use Symfony\Component\Serializer\Attribute\SerializedName;

class ProposalProduct
{
    #[SerializedName('sku')]
    private ProductCode $productCode;

    private string $ean = '';

    private string $reference = '';

    private string $name;

    private int $units;

    #[SerializedName('unitPrice')]
    private float $unitPriceTaxExcl;

    private int $stockAvailable = 0;

    #[SerializedName('iva')]
    private ?Tax $tax;

    private int $minimalQuantity = 0;

    private string $url;

    private float $weight;

    private float $volume;

    private float $discount;

    private float $tariff;

    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    public function setProductCode(ProductCode $productCode): static
    {
        $this->productCode = $productCode;
        return $this;
    }


    public function setEan(string $ean): static
    {
        $this->ean = trim($ean);
        return $this;
    }

    public function setReference(string $reference): static
    {
        $this->reference = trim($reference);
        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = mb_strtoupper(trim($name));
        return $this;
    }

    public function setUnits(int $units): static
    {
        $this->units = $units;
        return $this;
    }

    public function setUnitPriceTaxExcl(float $unitPriceTaxExcl): static
    {
        $this->unitPriceTaxExcl = $unitPriceTaxExcl;
        return $this;
    }

    public function setStockAvailable(int $stockAvailable): static
    {
        $this->stockAvailable = $stockAvailable;
        return $this;
    }

    public function setTax(Tax $tax): static
    {
        $this->tax = $tax;
        return $this;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUnits(): int
    {
        return $this->units;
    }

    public function getUnitPriceTaxExcl(): float
    {
        return $this->unitPriceTaxExcl;
    }

    public function getStockAvailable(): int
    {
        return $this->stockAvailable;
    }

    public function getTax(): Tax
    {
        return $this->tax;
    }

    public function getMinimalQuantity(): int
    {
        return $this->minimalQuantity;
    }

    public function setMinimalQuantity(int $minimalQuantity): ProposalProduct
    {
        $this->minimalQuantity = max(1, $minimalQuantity);
        return $this;
    }

    public function getUnitPriceTaxInc(): float
    {
        return $this->tax->applyTaxToAmount($this->unitPriceTaxExcl);
    }

    public function getTotalPriceTaxExcl(): float
    {
        return (float)bcmul((string)$this->unitPriceTaxExcl, (string)$this->units, 6);
    }

    public function getTotalPriceTaxInc(): float
    {
        return $this->tax->applyTaxToAmount($this->getTotalPriceTaxExcl());
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): ProposalProduct
    {
        $this->url = $url;
        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): ProposalProduct
    {
        $this->weight = $weight;
        return $this;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }

    public function setVolume(float $volume): ProposalProduct
    {
        $this->volume = $volume;
        return $this;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): ProposalProduct
    {
        $this->discount = $discount;
        return $this;
    }

    public function getTariff(): float
    {
        return $this->tariff;
    }

    public function setTariff(float $tariff): ProposalProduct
    {
        $this->tariff = $tariff;
        return $this;
    }
}
