<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Command\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class SetDefaultProductWarehouseConsoleCommand
{
    public function __construct(
        private CommandBus $commandBus,
    )
    {
    }

    #[AsCommand(
        name: 'kpy:warehouse:default-cheapest',
        description: 'Establece el almacén con el coste más barato como el predeterminado',
    )]
    public function cheapestWarehouse(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->commandBus->execute('kpy.warehouse.command.reset_default_product_warehouse');

            $this->commandBus->execute('kpy.warehouse.command.set_cheapest_warehouse_as_default');

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
