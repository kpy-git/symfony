<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('kpy:warehouse:sync-cost-price-store-products')]
class SyncCostPriceStoreProductsConsoleCommand
{
    public function __invoke(
        WarehouseProductRepository $warehouseProductRepository,
        WarehouseFactory           $warehouseFactory,
        CommandBus                 $commandBus,
        InputInterface             $input,
        OutputInterface            $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $products = $warehouseProductRepository->findProductsInWarehouse($warehouseFactory->createFrom('TIENDA'));

            foreach ($products as $product) {
                $commandBus->execute('kpy.warehouse.command.update_cost_price_store_product', [
                    'sku' => ProductCode::from($product->getProductId(), $product->getProductAttributeId())->getSku(),
                    'cost' => $product->getFinalCostPrice(),
                ]);
            }

            $io->success(sprintf('Updated %d products cost', count($products)));

            return Command::SUCCESS;

        } catch (KpyException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
