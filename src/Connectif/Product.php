<?php

namespace App\Connectif;

use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;

class Product implements \JsonSerializable
{
    private float $rating = 0;

    private int $totalRatingCount = 0;

    private array $features = [];

    private array $tags = [];

    private string $name;

    private string $brand;

    private string $url;

    private float $price = 0;

    private float $priceOriginal;

    private string $availability;

    private array $categories = [];

    private array $relatedProducts = [];

    private bool $blackListed = false;

    private string $imageUrl = '';

    private bool $hasSpecialPrice = false;

    private float $taxRate;

    private function __construct(private readonly ProductCode $productCode)
    {
    }

    public static function fromRow(
        array $row,
        array $categories,
        array $productFeatures,
        array $relatedProducts,
        array $productRating,
        array $tags,
        float $salesPrices,
        int $firstImageId,
        Shop  $shop
    ): self
    {
        if (!isset($row['id_product'], $row['id_product_attribute'])) {
            throw new KpyInvalidProductCode('No se puede crear un producto ("' . self::class . '") sin el id_product y id_product_attribute');
        }

        $productCode = ProductCode::from($row['id_product'], $row['id_product_attribute']);

        $product = new self($productCode);

        $product
            ->setPrice($salesPrices)
            ->setCategories($categories)
            ->setHasSpecialPrice($row['special_price'] === 'si')
            ->setAvailability($row['quantity'] > 0 ? 'instock' : 'outofstock')
            ->setTaxRate($row['tax_rate'])
            ->setPriceOriginal($row['pvp_tax_included'])
            ->setBrand($row['brand'])
            ->setName(trim($row['name']))
            ->setFeatures($productFeatures)
            ->setRelatedProducts($relatedProducts)
            ->setTags($tags)
            ->setUrl(sprintf('https://%s/%s/%s-%s.html',
                $shop->getDomain(),
                $row['link_categoria'],
                $productCode->isCombinationProduct() ? $productCode->getSku() : $productCode->getProductId(),
                $row['link_producto'])
            );

        if ($productRating['count'] ?? 0) {
            $product->setRatings($productRating['count'], $productRating['rating']);
        }

        // Se marca como blackListed cuando:
        // si la combinación está desactivada
        // si el producto no está activo (active en ps_product)
        // si no está visible (es un producto oculto como los regalos)
        // si tiene el stock a 0
        // si es un pack (que no sea de boske)
        if ((int)$row['combination_active'] === 0
            || (int)$row['activo'] === 0
            || $row['quantity'] <= 0
            || $row['visibility'] !== 'both'
            || ($row['pack'] === 'si' && $row['brand'] !== 'Boske')
        ) {
            $product->setBlackListed(true);
        }

        if ($firstImageId > 0) {
            $product->setImageUrl("https://{$shop->getDomain()}/{$firstImageId}-large_default/{$row['link_producto']}.jpg");
        }

        return $product;
    }

    public function getProductId(): int
    {
        return $this->productCode->getProductId();
    }

    public function getSku(): string
    {
        return $this->productCode->getSku();
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setPriceOriginal(float $priceOriginal): self
    {
        $this->priceOriginal = $priceOriginal;
        return $this;
    }

    public function setAvailability(string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function setHasSpecialPrice(bool $hasSpecialPrice): self
    {
        $this->hasSpecialPrice = $hasSpecialPrice;
        return $this;
    }

    public function setTaxRate(float $taxRate): static
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function setRatings(int $totalRatingCount, float $rating): static
    {
        $this->rating = $rating;
        $this->totalRatingCount = $totalRatingCount;

        return $this;
    }

    public function setFeatures(array $features): static
    {
        $this->features = $features;
        return $this;
    }

    public function setRelatedProducts(array $relatedProducts): self
    {
        $this->relatedProducts = $relatedProducts;
        return $this;
    }

    public function setBlackListed(bool $blackListed): self
    {
        $this->blackListed = $blackListed;
        return $this;
    }

    public function isBlackListed(): bool
    {
        return $this->blackListed;
    }

    public function setImageUrl(string $url): self
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $url = str_replace(['ç', 'ã', 'õ', 'á', 'ú', 'õ', 'ó'], ['c', 'a', 'o', 'a', 'u', 'o', 'o'], $url);
        }

        $this->imageUrl = $url;
        return $this;
    }

    public function setUrl(string $url): self
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $url = str_replace(['ç', 'ã', 'õ', 'á', 'ú', 'õ', 'ó'], ['c', 'a', 'o', 'a', 'u', 'o', 'o'], $url);
        }

        $this->url = $url;
        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'name' => $this->name,
            'brand' => $this->brand,
            'productDetailUrl' => $this->url,
            'unitPrice' => $this->price,
            'unitPriceOriginal' => $this->priceOriginal,
            'unitPriceWithoutVAT' => round($this->price / $this->taxRate, 2),
            'discountedAmount' => $this->priceOriginal > 0 ? round($this->priceOriginal - $this->price, 2) : 0,
            'discountedPercentage' => $this->priceOriginal > 0 ? round((1 - ($this->price / $this->priceOriginal)) * 100, 2) : 0,
            'availability' => $this->availability,
            'categories' => $this->categories,
            'relatedExternalProductIds' => $this->relatedProducts[$this->productCode->getProductId()] ?? [],
            'isBlacklisted' => $this->blackListed,
            'customField1' => '',
            'customField2' => $this->hasSpecialPrice ? 'precio_especial' : '',
            'imageUrl' => $this->imageUrl,
            'tags' => array_merge(
                $this->features,
                ['Marca | ' . $this->brand],
                $this->tags,
            ),
        ];

        if ($this->totalRatingCount > 0) {
            $data['rating'] = $this->rating;
            $data['ratingCount'] = $this->totalRatingCount;
        }

        return $data;
    }
}
