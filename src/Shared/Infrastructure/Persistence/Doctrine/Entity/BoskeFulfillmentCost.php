<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BoskeFulfillmentCostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoskeFulfillmentCostRepository::class)]
#[ORM\Table(name: 'kpy_boske_fulfillment_cost')]
class BoskeFulfillmentCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:'single_item_up_to_5kg', type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $singleItemUpTo5Kg = null;

    #[ORM\Column(name:'single_item_starting_at_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $singleItemStartingAt5Kg = null;

    #[ORM\Column(name:'additional_items_up_to_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $additionalItemsUpTo5Kg = null;

    #[ORM\Column(name:'additional_items_starting_at_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $additionalItemsStartingAt5Kg = null;

    #[ORM\ManyToOne(inversedBy: 'boskeFulfillmentCosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $warehouse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSingleItemUpTo5Kg(): ?string
    {
        return $this->singleItemUpTo5Kg;
    }

    public function setSingleItemUpTo5Kg(string $singleItemUpTo5Kg): static
    {
        $this->singleItemUpTo5Kg = $singleItemUpTo5Kg;

        return $this;
    }

    public function getSingleItemStartingAt5Kg(): ?string
    {
        return $this->singleItemStartingAt5Kg;
    }

    public function setSingleItemStartingAt5Kg(string $singleItemStartingAt5Kg): static
    {
        $this->singleItemStartingAt5Kg = $singleItemStartingAt5Kg;

        return $this;
    }

    public function getAdditionalItemsUpTo5Kg(): ?string
    {
        return $this->additionalItemsUpTo5Kg;
    }

    public function setAdditionalItemsUpTo5Kg(string $additionalItemsUpTo5Kg): static
    {
        $this->additionalItemsUpTo5Kg = $additionalItemsUpTo5Kg;

        return $this;
    }

    public function getAdditionalItemsStartingAt5Kg(): ?string
    {
        return $this->additionalItemsStartingAt5Kg;
    }

    public function setAdditionalItemsStartingAt5Kg(string $additionalItemsStartingAt5Kg): static
    {
        $this->additionalItemsStartingAt5Kg = $additionalItemsStartingAt5Kg;

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
}
