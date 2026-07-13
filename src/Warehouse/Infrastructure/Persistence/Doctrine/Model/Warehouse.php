<?php

namespace App\Warehouse\Infrastructure\Persistence\Doctrine\Model;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WarehouseRepository;
use App\Warehouse\Domain\CostStrategy\CostStrategyType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $carrierService = null;

    #[ORM\Column(enumType: CostStrategyType::class)]
    private CostStrategyType $costStrategyType;

    /**
     * @var Collection<int, PackageModel>
     */
    #[ORM\OneToMany(targetEntity: PackageModel::class, mappedBy: 'warehouse', orphanRemoval: true)]
    private Collection $packages;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
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
        $this->name = mb_strtoupper($name);

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

    public function getCostStrategyType(): CostStrategyType
    {
        return $this->costStrategyType;
    }

    public function setCostStrategyType(CostStrategyType $costStrategyType): static
    {
        $this->costStrategyType = $costStrategyType;
        return $this;
    }

    /**
     * @return Collection<int, PackageModel>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(PackageModel $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setWarehouse($this);
        }

        return $this;
    }

    public function removePackage(PackageModel $package): static
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getWarehouse() === $this) {
                $package->setWarehouse(null);
            }
        }

        return $this;
    }

}
