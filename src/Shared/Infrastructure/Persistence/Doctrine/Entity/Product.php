<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'kpy_product')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(name: "id_product", options: ["unsigned" => true])]
    private ?int $productId = null;

    #[ORM\Id]
    #[ORM\Column(name: "id_product_attribute", options: ["unsigned" => true])]
    private ?int $productAttributeId = null;

    #[ORM\Column(options: ["default" => 0])]
    private bool $isJirafa = false;

    #[ORM\Column(options: ["default" => 0])]
    private bool $isPack = false;

    #[ORM\Column(options: ["default" => 0])]
    private float $weight = 0;

    #[ORM\Column(type: "smallint", options: ['unsigned' => true])]
    private ?int $brandId = null;


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

    public function isJirafa(): ?bool
    {
        return $this->isJirafa;
    }

    public function setIsJirafa(bool $isJirafa): static
    {
        $this->isJirafa = $isJirafa;

        return $this;
    }

    public function isPack(): bool
    {
        return $this->isPack;
    }

    public function setIsPack(bool $isPack): static
    {
        $this->isPack = $isPack;
        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getBrandId(): int
    {
        return $this->brandId;
    }

    public function setBrandId(?int $brandId): static
    {
        $this->brandId = $brandId;
        return $this;
    }
}
