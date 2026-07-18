<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\ValueObject\WarehouseProductFulfillmentCost;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use App\Warehouse\Service\FulfillmentProductCostCalculator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ComputeProductFulfillmentCostByWarehouseConsoleCommand
{
    public function __construct(
        private WarehouseFactory                 $warehouseFactory,
        private FulfillmentProductCostCalculator $fulfillmentProductCostCalculator,
        private WarehouseProductRepository       $warehouseProductRepository,
    )
    {
    }

    #[AsCommand('kpy:warehouse:warehouse-fulfillment-cost')]
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Argument]
        #[Ask('Nombre del almacén [string]')]
        string $warehouseName,
        #[Option]
        bool $persist = false,
        #[Option]
        bool $print = false
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $warehouse = $this->warehouseFactory->createFrom($warehouseName);
            $productsFulfillmentCost = $this->fulfillmentProductCostCalculator->computeFulfillmentCostByWarehouse($warehouse);
            $count = 0;
            $dataForPersist = [];

            foreach ($productsFulfillmentCost as $productFulfillmentCost) {
                if ($print) {
                    $io->writeln(json_encode($productFulfillmentCost));
                }

                $count++;

                if ($persist) {
                    $dataForPersist[] = new WarehouseProductFulfillmentCost(
                        $productFulfillmentCost->getProductCode(),
                        $warehouse->getId(),
                        $productFulfillmentCost->getTotalCost()
                    );
                }
            }

            if (!empty($dataForPersist)) {
                $this->warehouseProductRepository->updateProductsFulfillmentCostBatch($dataForPersist);
            }

            $io->success(sprintf('%d productos en el almacén', $count));

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
