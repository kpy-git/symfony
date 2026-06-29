<?php

namespace App\Connectif\Infrastructure\Persistence\Doctrine\Model;

use App\Connectif\Infrastructure\Persistence\Doctrine\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'connectif_product')]
class Product
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_product = null;

    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_product_attribute = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sync_at = null;

    #[ORM\Column(nullable: true)]
    private ?array $extra_tags = null;

    public function getIdProduct(): ?int
    {
        return $this->id_product;
    }

    public function setIdProduct(int $id_product): static
    {
        $this->id_product = $id_product;

        return $this;
    }

    public function getIdProductAttribute(): ?int
    {
        return $this->id_product_attribute;
    }

    public function setIdProductAttribute(int $id_product_attribute): static
    {
        $this->id_product_attribute = $id_product_attribute;

        return $this;
    }

    public function getSyncAt(): ?\DateTimeImmutable
    {
        return $this->sync_at;
    }

    public function setSyncAt(\DateTimeImmutable $sync_at): static
    {
        $this->sync_at = $sync_at;

        return $this;
    }

    public function getExtraTags(): ?array
    {
        return $this->extra_tags;
    }

    public function setExtraTags(?array $extra_tags): static
    {
        $this->extra_tags = $extra_tags;

        return $this;
    }
}
