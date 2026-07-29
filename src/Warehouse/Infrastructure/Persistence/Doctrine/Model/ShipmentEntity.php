<?php

namespace App\Warehouse\Infrastructure\Persistence\Doctrine\Model;

use App\Warehouse\Infrastructure\Persistence\Doctrine\Repository\ShipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipmentRepository::class)]
#[ORM\Table(name: 'warehouse_shipment')]
#[ORM\HasLifecycleCallbacks]
class ShipmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'id_order')]
    private ?int $orderId = null;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $label = null;

    #[ORM\Column(length: 255)]
    private ?string $trackingNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getLabel(): ?string
    {
        return stream_get_contents($this->label);
    }

    public function setLabel(mixed $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
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
