<?php

namespace App\Priceshape\Infrastructure\Persistence\Doctrine\Model;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Repository\BrandBannedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandBannedRepository::class)]
#[ORM\Table(name: "priceshape_brand_banned")]
#[ORM\HasLifecycleCallbacks]
class BrandBanned
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id_manufacturer;

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

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTimeImmutable();
        return $this;
    }
}
