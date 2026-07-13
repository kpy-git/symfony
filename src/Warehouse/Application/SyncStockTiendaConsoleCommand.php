<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Query\QueryBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class SyncStockTiendaConsoleCommand
{
    public function __construct(
        private QueryBus $queryBus,
        private CommandBus $commandBus,
    )
    {
    }

    #[AsCommand('kpy:warehouse:sync:stock-tienda')]
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $stockTienda = $this->queryBus->fetch('kpy.warehouse.query.stock_kompy_chinales');

        $skuInNeftys = array_map(
            static fn (array $row): string => $row['sku'],
            $this->queryBus->fetch('kpy.warehouse.query.stock_neftys')
        );

        $duplicated = 0;
        $updated = 0;

        foreach ($stockTienda as $product) {
            try {
                $productCode = ProductCode::fromSKU($product['SKU']);
                if (in_array($product['SKU'], $skuInNeftys)) {
                    $duplicated++;
                }

                $this->commandBus->execute('kpy.warehouse.command.update_prestashop_stock', [
                    'product_code' => $productCode,
                    'quantity' => (int)$product['STOCK'],
                ]);
                $updated++;
            } catch (KpyInvalidProductCode $e) {
                continue;
            }
        }

        $io = new SymfonyStyle($input, $output);

        $io->success($updated . ' products(s) updated');

        $io->info($duplicated . ' product(s) duplicated with Neftys');

        return Command::SUCCESS;
    }

}
