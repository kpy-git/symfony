<?php

namespace App\Entity;

use App\Repository\WarehouseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
#[Orm\Table(name: 'kpy_warehouse')]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $carrierService = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $commission = null;

    #[ORM\Column]
    private ?bool $packagingIncluded = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $fixedCostForSmallItem = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?BoskeFulfillmentCost $boskeFulfillmentCost = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCarrierService(): ?string
    {
        return $this->carrierService;
    }

    public function setCarrierService(string $carrierService): static
    {
        $this->carrierService = $carrierService;

        return $this;
    }

    public function getCommission(): ?string
    {
        return $this->commission;
    }

    public function setCommission(string $commission): static
    {
        $this->commission = $commission;

        return $this;
    }

    public function isPackagingIncluded(): ?bool
    {
        return $this->packagingIncluded;
    }

    public function setPackagingIncluded(bool $packagingIncluded): static
    {
        $this->packagingIncluded = $packagingIncluded;

        return $this;
    }

    public function getFixedCostForSmallItem(): ?string
    {
        return $this->fixedCostForSmallItem;
    }

    public function setFixedCostForSmallItem(string $fixedCostForSmallItem): static
    {
        $this->fixedCostForSmallItem = $fixedCostForSmallItem;

        return $this;
    }

    public function getBoskeFulfillmentCost(): ?BoskeFulfillmentCost
    {
        return $this->boskeFulfillmentCost;
    }

    public function setBoskeFulfillmentCost(?BoskeFulfillmentCost $boskeFulfillmentCost): static
    {
        $this->boskeFulfillmentCost = $boskeFulfillmentCost;

        return $this;
    }
}
