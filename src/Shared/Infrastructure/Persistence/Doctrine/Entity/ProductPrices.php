<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ProductPricesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPricesRepository::class)]
#[ORM\Table(name: 'kpy_product_prices')]
class ProductPrices
{
    #[ORM\Id]
    #[ORM\Column(name: "id_product", options: ["unsigned" => true])]
    private ?int $productId = null;

    #[ORM\Id]
    #[ORM\Column(name: "id_product_attribute", options: ["unsigned" => true])]
    private ?int $productAttributeId = null;

    #[ORM\Column(name: 'sales_price_es', type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $salesPriceES = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $finalCostPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $pcmp = null;


    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getProductAttributeId(): ?int
    {
        return $this->productAttributeId;
    }

    public function setProductAttributeId(int $productAttributeId): static
    {
        $this->productAttributeId = $productAttributeId;

        return $this;
    }

    public function getSalesPriceES(): ?string
    {
        return $this->salesPriceES;
    }

    public function setSalesPriceES(string $salesPriceES): static
    {
        $this->salesPriceES = $salesPriceES;

        return $this;
    }

    public function getFinalCostPrice(): ?string
    {
        return $this->finalCostPrice;
    }

    public function setFinalCostPrice(string $finalCostPrice): static
    {
        $this->finalCostPrice = $finalCostPrice;

        return $this;
    }

    public function getPcmp(): ?string
    {
        return $this->pcmp;
    }

    public function setPcmp(string $pcmp): static
    {
        $this->pcmp = $pcmp;

        return $this;
    }
}
