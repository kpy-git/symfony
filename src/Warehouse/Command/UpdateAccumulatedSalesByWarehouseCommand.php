<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateAccumulatedSalesByWarehouseCommand implements CommandInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_accumulated_sales_by_warehouse';
    }

    public function execute(array $params = []): int
    {
        $this->aquaDatabase->execute("TRUNCATE TABLE DATKPYVENTASACCALM03");

        return $this->aquaDatabase->insertMany('DATKPYVENTASACCALM03', $params['accumulated_sales'], 100);
    }
}
