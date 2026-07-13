<?php

namespace App\Warehouse\Application;

use App\Warehouse\Domain\WarehouseFactory;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;

class ComputeProductCostByWarehouseConsoleCommand
{
    public function __construct(
        private readonly WarehouseFactory $warehouseFactory,
    )
    {
    }

    #[AsCommand('kpy:warehouse:compute_all_product_cost')]
    public function __invoke(
        #[Argument]
        #[Ask('Nombre del almacén [string]')]
        string $warehouseName,
    ): int
    {
        $warehouse = $this->warehouseFactory->createFrom($warehouseName);

        return Command::SUCCESS;
    }
}
