<?php

namespace App\Connectif;

use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\ValueObject\ProductCode;

class Product implements \JsonSerializable
{
    private float $raiting;

    private int $totalRaitingCount;

    private array $features = [];

    private array $tags;

    private function __construct(private readonly ProductCode $productCode)
    {
    }

    public static function fromRow(array $row): self
    {
        if (isset($row['id_product'], $row['id_product_attribute'])) {
            throw new KpyInvalidProductCode('No se puede crear un producto ("' . self::class . '") sin el id_product y id_product_attribute');
        }

        $product = new self(ProductCode::from($row['id_product'], $row['id_product_attribute']));

        return $product;
    }

    public function setRaitings(int $totalRaitingCount, float $raiting): static
    {
        $this->raiting = $raiting;
        $this->totalRaitingCount = $totalRaitingCount;

        return $this;
    }

    public function setFeatures(array $features): static
    {
        $this->features = $features;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'sku' => $this->productCode->getSku(),
            'tags' => array_merge(
                empty($this->features) ? [] : $this->features,
            )
        ];

        if ($this->totalRaitingCount > 0) {
            $data['raiting'] = $this->raiting;
            $data['totalRaitingCount'] = $this->totalRaitingCount;
        }

        return $data;
    }
}
