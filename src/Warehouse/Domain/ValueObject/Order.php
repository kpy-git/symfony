<?php

namespace App\Warehouse\Domain\ValueObject;

class Order
{
    /** @var OrderProduct[] $products */
    private array $products;

    public function __construct(
        private readonly int                $orderId,
        private readonly \DateTimeImmutable $orderDate,
        private readonly OrderCustomer      $customer,
    )
    {
        $this->products = [];
    }

    public function addProduct(OrderProduct $newProduct): void
    {
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

}
