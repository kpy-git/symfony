<?php

namespace App\Warehouse\Command;


use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class SetCheapestWarehouseAsDefaultCommand implements CommandInterface
{
    public function __construct(
        private DatabaseInterface $doctrineDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.set_cheapest_warehouse_as_default';
    }

    public function execute(array $params = []): bool
    {
        return $this->doctrineDatabase->execute(
            "with products_ranked as (select id_product,
                   id_product_attribute,
                   warehouse_id,
                   row_number() over (partition by id_product, id_product_attribute order by fulfillment_price) as `position`
            from warehouse_product)
            update warehouse_product wp
            inner join products_ranked pr
                on pr.id_product = wp.id_product
                    and pr.id_product_attribute = wp.id_product_attribute
                    and wp.warehouse_id = pr.warehouse_id
            set wp.is_default = 1
            where pr.position = 1",
        );
    }
}
