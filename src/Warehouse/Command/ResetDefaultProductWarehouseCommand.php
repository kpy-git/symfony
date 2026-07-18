<?php

namespace App\Warehouse\Command;


use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class ResetDefaultProductWarehouseCommand implements CommandInterface
{
    public function __construct(
        private DatabaseInterface $doctrineDatabase,
    )
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.reset_default_product_warehouse';
    }

    public function execute(array $params = []): bool
    {
        return $this->doctrineDatabase->execute(
            "UPDATE warehouse_product SET is_default = 0 WHERE is_default = 1",
        );
    }
}
