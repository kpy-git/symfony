<?php

namespace App\Connectif;


use App\Connectif\Query\ConnectifQueryBus;

class PurchaseProvider
{
    public function __construct(
        private readonly ConnectifQueryBus $queryBus,
    )
    {
    }

    public function order(int $order): array
    {
        return $this->queryBus->fetch(
            'kpy.connectif.query.legacy_order_for_import',
            ['id_order' => $order]
        );
    }

    public function ordersFromPymLegacy(int $fromOrder): \PDOStatement
    {
        return $this->queryBus->fetch(
            'kpy.connectif.query.legacy_orders_for_import',
            $fromOrder > 0 ? ['first_order' => $fromOrder] : []
        );
    }

    public function productsOrderPymLegacy(int $orderId): array
    {
        return $this->queryBus->fetch('kpy.connectif.query.legacy_order_products', ['id_order' => $orderId]);
    }
}
