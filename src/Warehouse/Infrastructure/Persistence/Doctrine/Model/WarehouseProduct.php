<?php

namespace App\Warehouse\Infrastructure\Persistence\Doctrine\Model;

use App\Warehouse\Infrastructure\Persistence\Doctrine\Repository\WarehouseProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseProductRepository::class)]
#[ORM\Table(name: 'warehouse_product')]
class WarehouseProduct
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: "id_product", options: ["unsigned" => true])]
    private ?int $productId = null;

    #[ORM\Column(name: "id_product_attribute", options: ["unsigned" => true])]
    private ?int $productAttributeId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $warehouse = null;

    #[ORM\Column]
    private bool $isDefault = true;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}
