<?php

namespace App\Connectif;

use App\Shared\Domain\Shop;
use App\Shared\Domain\ValueObject\ProductCode;

class Purchase implements \JsonSerializable
{
    private int $productsQuantity = 0;

    private array $products = [];

    private function __construct(
        private string $email,
        private string $date,
        private string $paymentMethod,
        private int    $cartId,
        private int    $orderId,
        private float  $orderTotal,
        private Shop   $shop)
    {
    }

    public static function from(array $purchaseRaw, array $productsRaw, Shop $shop): self
    {
        $purchase = new self(
            $purchaseRaw['email'],
            $purchaseRaw['fecha'],
            $purchaseRaw['payment'],
            $purchaseRaw['id_cart'],
            $purchaseRaw['id_order'],
            (float)$purchaseRaw['total_paid'],
            $shop,
        );

        foreach ($productsRaw as $product) {
            $purchase->addProduct($product);
        }

        return $purchase;
    }

    private function addProduct(array $product): void
    {
        $productCode = ProductCode::fromSKU($product['SKU']);
        $skuUrl = $productCode->isCombinationProduct() ? $productCode->getSku() : $productCode->getProductId();

        $this->products[] = [
            'name' => str_replace('- Formato : ', '', $product['product_name']),
            'brand' => $product['brand'],
            'unitPrice' => (float)$product['unit_price'],
            'productDetailUrl' => 'https://' . $this->shop->getDomain() . '/' . $product['category_link'] . '/' . $skuUrl . '-' .
                $product['product_link'] . '.html',
            'productId' => $product['SKU'],
            'quantity' => (int)$product['product_quantity'],
            'price' => (float)$product['total_price'],
            'categories' => $product['categories'],
            'tags' => array_merge(
                $product['features'],
                ['Marca | ' . $product['brand']],
            ),
        ];

        $this->productsQuantity += (int)$product['product_quantity'];
    }

    public function jsonSerialize(): array
    {
        return [
            'cartId' => (string)$this->cartId,
            'contactEmail' => $this->email,
            'paymentMethod' => $this->paymentMethod,
            'products' => $this->products,
            'purchaseDate' => date(DATE_ATOM, strtotime($this->date)),
            'purchaseId' => (string)$this->orderId,
            'totalPrice' => $this->orderTotal,
            'totalQuantity' => $this->productsQuantity,
        ];
    }
}
