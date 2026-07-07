<?php

namespace App\Priceshape\Infrastructure\Persistence\Doctrine\Model;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Repository\BrandFixedPriceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandFixedPriceRepository::class)]
#[ORM\Table(name: "priceshape_brand_fixed_price")]
#[ORM\HasLifecycleCallbacks]
class BrandFixedPrice
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_manufacturer = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getIdManufacturer(): ?int
    {
        return $this->id_manufacturer;
    }

    public function setIdManufacturer(int $id_manufacturer): static
    {
        $this->id_manufacturer = $id_manufacturer;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTimeImmutable();
        return $this;
    }
}
