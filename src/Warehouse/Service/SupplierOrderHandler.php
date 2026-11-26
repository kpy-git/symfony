<?php

namespace App\Warehouse\Service;

use App\Warehouse\Domain\OrderBuy;
use App\Warehouse\Infrastructure\Persistence\Repository\AquaOrderBuyRepository;
use App\Warehouse\Infrastructure\Persistence\Repository\SupplierRepository;

readonly class SupplierOrderHandler
{
    public function __construct(
        private SupplierRepository $supplierRepository,
        private AquaOrderBuyRepository $aquaOrderBuyRepository
    )
    {
    }

    public function createOrder(string $supplierCode, array $products): OrderBuy
    {
        $supplier = $this->supplierRepository->findByNifOrFail($supplierCode);

        $order = new OrderBuy();
        $order->setProveedor($supplier);

        foreach ($products as $product) {
            $order->agregaProducto($product);
        }

        return $order;
    }

    public function save(OrderBuy $order): void
    {
        $this->aquaOrderBuyRepository->save($order);
    }
}
