<?php

namespace App\Priceshape\Infrastructure\Persistence\Doctrine\Model;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Repository\BrandIncludedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandIncludedRepository::class)]
#[ORM\Table(name: "priceshape_brand_included")]
#[ORM\HasLifecycleCallbacks]
class BrandIncluded
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id_manufacturer;

    #[ORM\Column]
    private bool $fixedPrice = false;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(int $id_manufacturer)
    {
        $this->id_manufacturer = $id_manufacturer;
    }

    public function getIdManufacturer(): ?int
    {
        return $this->id_manufacturer;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isFixedPrice(): bool
    {
        return $this->fixedPrice;
    }

    public function setFixedPrice(bool $fixedPrice): static
    {
        $this->fixedPrice = $fixedPrice;
        return $this;
    }



    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTimeImmutable();
        return $this;
    }
}
