<?php

namespace App\Warehouse\Domain\ValueObject;

class Order
{
    /** @var OrderProduct[] $products */
    private array $products;

    private float $weight;

    private float $crm;

    public function __construct(
        private readonly int                $orderId,
        private readonly \DateTimeImmutable $orderDate,
        private readonly OrderCustomer      $customer,
    )
    {
        $this->products = [];
        $this->weight = 0;
        $this->crm = 0;
    }

    public function addProduct(OrderProduct $newProduct): void
    {
        $this->weight += $newProduct->getQuantity() * $newProduct->getWeight();

        if (empty($this->products)) {
            $this->products[] = $newProduct;
            return;
        }

        foreach ($this->products as $product) {
            if ($product->equals($newProduct)) {
                $this->products[] = new OrderProduct(
                    $product->getProductCode(),
                    $product->getName(),
                    $product->getQuantity() + $newProduct->getQuantity(),
                    $product->getEan()
                );
                continue;
            }

            $this->products[] = $newProduct;
        }
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOrderDate(): \DateTimeImmutable
    {
        return $this->orderDate;
    }

    public function getCustomer(): OrderCustomer
    {
        return $this->customer;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function isCRM(): bool
    {
        return $this->crm > 0;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getCrm(): float
    {
        return $this->crm;
    }

    public function setCrm(float $crm): static
    {
        $this->crm = $crm;
        return $this;
    }

    public function getNotes(): string
    {
        return 'Observaciones del cliente';
    }

}
