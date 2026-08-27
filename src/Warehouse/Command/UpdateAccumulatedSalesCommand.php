<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class UpdateAccumulatedSalesCommand implements CommandInterface
{
    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.update_accumulated_sales';
    }

    public function execute(array $params = []): int
    {
        $this->aquaDatabase->execute("TRUNCATE TABLE DATKPYVENTASACC03");

        return $this->aquaDatabase->insertMany('DATKPYVENTASACC03', $params['accumulated_sales']);
    }
}
