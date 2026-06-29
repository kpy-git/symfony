<?php

namespace App\Connectif\Infrastructure\Persistence\Doctrine\Model;

use App\Connectif\Infrastructure\Persistence\Doctrine\Repository\ProductRelatedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRelatedRepository::class)]
#[ORM\Table(name: 'connectif_product_related')]
class ProductRelated
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_product_related = null;

    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_product_attribute_related = null;

    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_product = null;

    public function getIdProductRelated(): ?int
    {
        return $this->id_product_related;
    }

    public function setIdProductRelated(int $id_product_related): static
    {
        $this->id_product_related = $id_product_related;

        return $this;
    }

    public function getIdProductAttributeRelated(): ?int
    {
        return $this->id_product_attribute_related;
    }

    public function setIdProductAttributeRelated(int $id_product_attribute_related): static
    {
        $this->id_product_attribute_related = $id_product_attribute_related;

        return $this;
    }

    public function getIdProduct(): ?int
    {
        return $this->id_product;
    }

    public function setIdProduct(int $id_product): static
    {
        $this->id_product = $id_product;

        return $this;
    }
}
