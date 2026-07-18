<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\ValueObject\ProductFulfillmentCost;
use App\Warehouse\Domain\ValueObject\WarehouseProductFulfillmentCost;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use App\Warehouse\Service\FulfillmentProductCostCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ComputeAllProductsFulfillmentCostAllWarehouseConsoleCommand
{
    public function __construct(
        private WarehouseFactory                 $warehouseFactory,
        private FulfillmentProductCostCalculator $fulfillmentProductCostCalculator,
        private WarehouseProductRepository       $warehouseProductRepository,
    )
    {
    }

    #[AsCommand('kpy:warehouse:all-fulfillment-cost')]
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $warehouses = $this->warehouseFactory->getAll();

            $dataForPersist = [];

            foreach ($warehouses as $warehouse) {
                $dataForPersist = [...$dataForPersist, ...array_map(
                    static function (ProductFulfillmentCost $cost) use ($warehouse): WarehouseProductFulfillmentCost {
                        return new WarehouseProductFulfillmentCost(
                            $cost->getProductCode(),
                            $warehouse->getId(),
                            $cost->getTotalCost()
                        );
                    }, $this->fulfillmentProductCostCalculator->computeFulfillmentCostByWarehouse($warehouse))
                ];
            }

            $this->warehouseProductRepository->updateProductsFulfillmentCostBatch($dataForPersist);

            $io->success(count($dataForPersist) ." costes calculados");

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
