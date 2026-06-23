<?php

namespace App\ShippingCostCalculator\Infrastructure\Persistence\Doctrine\Entity;

use App\ShippingCostCalculator\Infrastructure\Persistence\Doctrine\Repository\CarrierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarrierRepository::class)]
#[ORM\Table(name: 'shipping_cost_carrier')]
class Carrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $idServiceAqua = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $maxShippingWeight = null;

    #[ORM\Column]
    private ?bool $multiparcelAllowed = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $maxParcelWeight = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getIdServiceAqua(): ?int
    {
        return $this->idServiceAqua;
    }

    public function setIdServiceAqua(?int $idServiceAqua): static
    {
        $this->idServiceAqua = $idServiceAqua;
        return $this;
    }

    public function getMaxShippingWeight(): ?string
    {
        return $this->maxShippingWeight;
    }

    public function setMaxShippingWeight(?string $maxShippingWeight): static
    {
        $this->maxShippingWeight = $maxShippingWeight;
        return $this;
    }

    public function isMultiparcelAllowed(): ?bool
    {
        return $this->multiparcelAllowed;
    }

    public function setMultiparcelAllowed(?bool $multiparcelAllowed): static
    {
        $this->multiparcelAllowed = $multiparcelAllowed;
        return $this;
    }

    public function getMaxParcelWeight(): ?string
    {
        return $this->maxParcelWeight;
    }

    public function setMaxParcelWeight(?string $maxParcelWeight): static
    {
        $this->maxParcelWeight = $maxParcelWeight;
        return $this;
    }
}
