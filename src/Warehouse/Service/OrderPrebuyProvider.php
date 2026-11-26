<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Service\UrlGenerator;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Query\QueryBus;

readonly class OrderPrebuyProvider
{
    public function __construct(
        private QueryBus $queryBus,
        private UrlGenerator $urlGenerator
    )
    {
    }

    public function getOrdersPendingConfirmationList(): array
    {
        $results = $this->queryBus->fetch('kpy.warehouse.query.prebuy_orders_list');

        if (empty($results)) {
            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'order_id' => (int)$row['PEDIDO'],
                'date' => substr($row['FECHA'], 0, strpos($row['FECHA'], " ") + 1) . $row['HORA'],
                'operation' => (int)$row['NUMERO'],
            ];
        }, $results);
    }

    public function getProductsByOperation(int $operation): array
    {
        return array_map( function (array $row): array {
            return [
                'sku' => $row['SKU'],
                'name' => ucwords(mb_strtolower($row['PRODUCTO'])),
                'brand' => ucwords(mb_strtolower($row['MARCA'])),
                'units' => (int)$row['UNIDADES'],
                'stock' => max(0, (int)$row['UNIDADES'] + (int)$row['STOCK']),
                'stock_class' => $this->getStockClass((int)$row['UNIDADES'], (int)$row['UNIDADES'] + (int)$row['STOCK']),
                'url' => $this->urlGenerator->generateProductLink(ProductCode::fromSKU($row['SKU']))
            ];
        }, $this->queryBus->fetch('kpy.warehouse.query.prebuy_order_products', [
            'operation' => $operation
        ]));
    }

    private function getStockClass(int $units, int $stock): string
    {
        return match (true) {
            $stock >= $units => 'is-sufficient',
            $stock > 0 && $units - $stock < 0 => 'is-low',
            default => 'is-unavailable',
        };
    }
}
