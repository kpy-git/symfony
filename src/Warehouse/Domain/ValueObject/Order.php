<?php

namespace App\Warehouse\Domain\ValueObject;

class Order
{
    /** @var OrderProduct[] $products */
    private array $products;

    private float $weight;

    private float $crm;

    private string $notes;

    public function __construct(
        private readonly int                $orderId,
        private readonly \DateTimeImmutable $orderDate,
        private readonly OrderCustomer      $customer,
    )
    {
        $this->products = [];
        $this->weight = 0;
        $this->crm = 0;
        $this->notes = '';
    }

    public function addProduct(OrderProduct $newProduct): void
    {
        $this->weight += $newProduct->getQuantity() * $newProduct->getWeight();

        if (empty($this->products)) {
            $this->products[] = $newProduct;
            return;
        }

        $newProducts = [];
        $inserted = false;

        foreach ($this->products as $product) {
            if ($product->equals($newProduct)) {
                $newProducts[] = new OrderProduct(
                    $product->getProductCode(),
                    $product->getName(),
                    $product->getQuantity() + $newProduct->getQuantity(),
                    $product->getEan(),
                    $product->getWeight(),
                );
                $inserted = true;
                continue;
            }

            $newProducts[] = $product;
        }

        if (!$inserted) {
            $newProducts[] = $newProduct;
        }

        $this->products = [...$newProducts];
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
        return max(floor($this->weight), 1);
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
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        /**
         * Elimina todos los caracteres excepto:
         * - números
         * - letras (las tildes y la ñ están permitidas)
         * - ([espacios].,-_/)
         *
         * Elimina saltos de línea/espacios innecesarios.
         * /u del final garantiza que las tildes y caracteres multibyte se procesen correctamente y no rompa nada
         */
        $this->notes = preg_replace('/[^\p{L}\p{N}\s.,\-_\/]/u', '', $notes)
                |> (static fn($x) => preg_replace('/\s+/', ' ', $x))
                |> trim(...);

        return $this;
    }
}
