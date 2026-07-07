<?php

namespace App\Priceshape\Domain;

#[\AllowDynamicProperties]
class Product implements \JsonSerializable
{
    private string $sku = '';

    private string $title = '';

    private string $image_url = '';

    private float $sale_price = 0.0;

    private float $cost_price = 0.0;

    private float $shipping_price = 0.0;

    private string $stock_group = '';

    private string $availability = '';

    private string $gtin = '';

    private string $brand = '';

    private string $mpn = '';

    private float $weight = 0.0;

    private int $vat = 10;

    private string $product_link = '';

    private string $product_type = 'Alimentacion para mascotas';

    private string $pet = '';

    private string $category = '';

    private string $promoType = '';

    private string $promoValue = '';

    private int $sales_last_30_days = 0;

    private int $brand_ranking = 999;

    private string $fixed_price = 'NO';

    private int $buyers = 0;

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getImageUrl(): string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }

    public function getSalePrice(): float
    {
        return $this->sale_price;
    }

    public function setSalePrice(float $sale_price): self
    {
        $this->sale_price = $sale_price;
        return $this;
    }

    public function getCostPrice(): float
    {
        return $this->cost_price;
    }

    public function setCostPrice(float $cost_price): self
    {
        $this->cost_price = round($cost_price, 2);
        return $this;
    }

    public function getStockGroup(): string
    {
        return $this->stock_group;
    }

    public function setStockGroup(string $stock_group): self
    {
        $this->stock_group = $stock_group;
        return $this;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getGtin(): string
    {
        return $this->gtin;
    }

    public function setGtin(string $gtin): self
    {
        $this->gtin = $gtin;
        return $this;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getMpn(): string
    {
        return $this->mpn;
    }

    public function setMpn(string $mpn): self
    {
        $this->mpn = $mpn;
        return $this;
    }

    public function getShippingPrice(): float
    {
        return $this->shipping_price;
    }

    public function setShippingPrice(float $shipping_price): self
    {
        $this->shipping_price = round($shipping_price, 2);
        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getVat(): int
    {
        return $this->vat;
    }

    public function setVAT(int $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    public function getProductLink(): string
    {
        return $this->product_link;
    }

    public function setProductLink(string $product_link): self
    {
        $this->product_link = $product_link;
        return $this;
    }

    public function getProductType(): string
    {
        return $this->product_type;
    }

    public function setProductType(string $product_type): self
    {
        $this->product_type = $product_type;
        return $this;
    }

    public function getPet(): string
    {
        return $this->pet;
    }

    public function setPet(string $pet): self
    {
        $this->pet = $pet;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getPromoType(): string
    {
        return $this->promoType;
    }

    public function setPromoType(string $promoType): self
    {
        $this->promoType = $promoType;
        return $this;
    }

    public function getPromoValue(): string
    {
        return $this->promoValue;
    }

    public function setPromoValue(string $promoValue): self
    {
        $this->promoValue = $promoValue;
        return $this;
    }

    public function getSalesLast30Days(): int
    {
        return $this->sales_last_30_days;
    }

    public function setSalesLast30Days(int $sales_last_30_days): self
    {
        $this->sales_last_30_days = $sales_last_30_days;
        return $this;
    }

    public function getBrandRanking(): int
    {
        return $this->brand_ranking;
    }

    public function setBrandRanking(int $brand_ranking): self
    {
        $this->brand_ranking = $brand_ranking ?: 999;
        return $this;
    }

    public function getFixedPrice(): string
    {
        return $this->fixed_price;
    }

    public function setFixedPrice(bool $fixed_price): self
    {
        $this->fixed_price = $fixed_price ? 'YES' : 'NO';
        return $this;
    }

    public function getBuyers(): int
    {
        return $this->buyers;
    }

    public function setBuyers(int $buyers): self
    {
        $this->buyers = $buyers;
        return $this;
    }


    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function __isset($name): bool
    {
        return isset($this->$name);
    }

    public function __get($name): mixed
    {
        return $this->$name;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
