<?php

namespace App\Priceshape\Infrastructure\Persistence\Doctrine\Model;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Repository\PvprRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PvprRepository::class)]
#[ORM\Table(name: 'priceshape_product_pvpr')]
class Pvpr
{
    #[ORM\Id]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id_product = null;

    #[ORM\Id]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id_product_attribute = null;

    #[ORM\Column(length: 2)]
    private ?string $country = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $pvpr = null;


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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPvpr(): ?string
    {
        return $this->pvpr;
    }

    public function setPvpr(string $pvpr): static
    {
        $this->pvpr = $pvpr;

        return $this;
    }
}
