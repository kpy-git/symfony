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

    #[ORM\Column]
    private ?bool $isJirafa = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $warehouse = null;

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

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;

        return $this;
    }
}
