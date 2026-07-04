<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WarehouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, BoskeFulfillmentCost>
     */
    #[ORM\OneToMany(targetEntity: BoskeFulfillmentCost::class, mappedBy: 'warehouse', orphanRemoval: true)]
    private Collection $boskeFulfillmentCosts;

    public function __construct()
    {
        $this->boskeFulfillmentCosts = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, BoskeFulfillmentCost>
     */
    public function getBoskeFulfillmentCosts(): Collection
    {
        return $this->boskeFulfillmentCosts;
    }

    public function addBoskeFulfillmentCost(BoskeFulfillmentCost $boskeFulfillmentCost): static
    {
        if (!$this->boskeFulfillmentCosts->contains($boskeFulfillmentCost)) {
            $this->boskeFulfillmentCosts->add($boskeFulfillmentCost);
            $boskeFulfillmentCost->setWarehouse($this);
        }

        return $this;
    }

    public function removeBoskeFulfillmentCost(BoskeFulfillmentCost $boskeFulfillmentCost): static
    {
        if ($this->boskeFulfillmentCosts->removeElement($boskeFulfillmentCost)) {
            // set the owning side to null (unless already changed)
            if ($boskeFulfillmentCost->getWarehouse() === $this) {
                $boskeFulfillmentCost->setWarehouse(null);
            }
        }

        return $this;
    }
}
