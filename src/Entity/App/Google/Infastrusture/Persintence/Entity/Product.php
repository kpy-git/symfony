<?php

namespace App\Entity\App\Google\Infastrusture\Persintence\Entity;

use App\Repository\App\Google\Infastrusture\Persintence\Entity\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
