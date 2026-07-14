<?php

namespace App\Warehouse\Domain;

use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Domain\ValueObject\Order;
use App\Warehouse\Domain\ValueObject\OrderCustomer;
use App\Warehouse\Domain\ValueObject\OrderProduct;
use App\Warehouse\Query\QueryBus;

readonly class OrderFactory
{
    public function __construct(private QueryBus $queryBus)
    {
    }

    /**
     * @throws KpyQueryNotFoundException
     */
    public function from(int $orderId): Order
    {
        $header = $this->queryBus->fetch('kpy.warehouse.query.order_header', ['id_order' => $orderId]);

        $customer = new OrderCustomer(
            $header['name'],
            $header['email'],
            $header['phone'],
            $header['address'],
            $header['city'],
            $header['state'],
            $header['postcode'],
            $header['country']
        );

        $order = new Order(
            $orderId,
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $header['date_add']),
            $customer,
        );

        $lines = $this->queryBus->fetch('kpy.warehouse.query.order_products', ['id_order' => $orderId]);

        foreach ($lines as $line) {
            $order->addProduct(new OrderProduct(
                ProductCode::from($line['id_product'], $line['id_product_attribute']),
                $line['name'],
                $line['quantity'],
                $line['ean'],
            ));
        }

        return $order;
    }
}
