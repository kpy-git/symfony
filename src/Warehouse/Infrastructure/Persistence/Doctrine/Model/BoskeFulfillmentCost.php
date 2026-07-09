<?php

namespace App\Warehouse\Infrastructure\Persistence\Doctrine\Model;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BoskeFulfillmentCostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoskeFulfillmentCostRepository::class)]
#[ORM\Table(name: 'warehouse_boske_fulfillment_cost')]
class BoskeFulfillmentCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:'single_item_up_to_5kg', type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?float $singleItemUpTo5Kg = 0;

    #[ORM\Column(name:'single_item_starting_at_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?float $singleItemStartingAt5Kg = 0;

    #[ORM\Column(name:'additional_items_up_to_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?float $additionalItemsUpTo5Kg = 0;

    #[ORM\Column(name:'additional_items_starting_at_5kg', type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?float $additionalItemsStartingAt5Kg = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSingleItemUpTo5Kg(): ?float
    {
        return $this->singleItemUpTo5Kg;
    }

    public function setSingleItemUpTo5Kg(float $singleItemUpTo5Kg): static
    {
        $this->singleItemUpTo5Kg = $singleItemUpTo5Kg;

        return $this;
    }

    public function getSingleItemStartingAt5Kg(): ?float
    {
        return $this->singleItemStartingAt5Kg;
    }

    public function setSingleItemStartingAt5Kg(float $singleItemStartingAt5Kg): static
    {
        $this->singleItemStartingAt5Kg = $singleItemStartingAt5Kg;

        return $this;
    }

    public function getAdditionalItemsUpTo5Kg(): ?float
    {
        return $this->additionalItemsUpTo5Kg;
    }

    public function setAdditionalItemsUpTo5Kg(float $additionalItemsUpTo5Kg): static
    {
        $this->additionalItemsUpTo5Kg = $additionalItemsUpTo5Kg;

        return $this;
    }

    public function getAdditionalItemsStartingAt5Kg(): ?float
    {
        return $this->additionalItemsStartingAt5Kg;
    }

    public function setAdditionalItemsStartingAt5Kg(float $additionalItemsStartingAt5Kg): static
    {
        $this->additionalItemsStartingAt5Kg = $additionalItemsStartingAt5Kg;

        return $this;
    }
}
