<?php

namespace App\Google\Infrastructure\Persistence\Doctrine\Model;

use App\Google\Infrastructure\Persistence\Doctrine\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: "google_product")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "id_product", options: ["unsigned" => true])]
    private ?int $productId = null;

    #[ORM\Column(name: "id_product_attribute", options: ["unsigned" => true])]
    private ?int $productAttributeId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
