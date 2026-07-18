<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Domain\ValueObject\Product;
use App\Warehouse\Domain\Warehouse;
use App\Warehouse\Domain\WarehouseFactory;
use App\Warehouse\Infrastructure\API\KpyPublicApi;
use App\Warehouse\Infrastructure\Persistence\Repository\WarehouseProductRepository;
use App\Warehouse\Service\FulfillmentProductCostCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ComputeProductFulfillmentCostConsoleCommand
{
    public function __construct(
        private WarehouseFactory                 $warehouseFactory,
        private EntityManagerInterface           $entityManager,
        private FulfillmentProductCostCalculator $fulfillmentProductCostCalculator,
        private KpyPublicApi                     $kpyPublicApi,
        private WarehouseProductRepository       $warehouseProductRepository,
    )
    {
    }

    #[AsCommand('kpy:warehouse:product-fulfillment-cost')]
    public function __invoke(
        InputInterface  $input,
        OutputInterface $output,
        #[Option]
        bool            $persist = false,
    ): int
    {
        $helper = new QuestionHelper();
        $io = new SymfonyStyle($input, $output);

        try {
            $io->writeln('SKU [string]:');
            $questionSku = new Question('> ');
            $sku = $helper->ask($input, $output, $questionSku);
            $io->newLine();

            $productCode = ProductCode::fromSKU($sku);
            $product = $this->kpyPublicApi->getProduct($productCode);

            $io->writeln('Almacén (déjalo en blanco para usar todos los almacenes) [string]:');
            $questionWarehouse = new Question('> ');
            $warehouseName = $helper->ask($input, $output, $questionWarehouse);
            $io->newLine();

            if (empty($warehouseName)) {
                $warehouses = $this->warehouseFactory->getAll();
            } else {
                $warehouses = [$this->warehouseFactory->createFrom($warehouseName)];
            }

            /** @var Warehouse $warehouse */
            foreach ($warehouses as $warehouse) {
                $warehouseProductEntity = $this->warehouseProductRepository->findProductInWarehouse(
                    $productCode,
                    $warehouse
                );

                if (null === $warehouseProductEntity) {
                    // el producto no está en el almacén
                    continue;
                }

                $warehouseProduct = new Product(
                    $productCode,
                    $product->getBrandId(),
                    $product->getWeight(),
                    $warehouseProductEntity->getFinalCostPrice()
                );

                $fulfillmentCost = $this->fulfillmentProductCostCalculator->computeFulfillmentCostByProduct(
                    $warehouseProduct,
                    $warehouse,
                );

                $io->writeln(sprintf("%s: %s %s", $warehouse->getName(), $fulfillmentCost, json_encode($fulfillmentCost)));

                if ($persist) {
                    $warehouseProductEntity->setFulfillmentPrice((string)$fulfillmentCost);
                    $this->entityManager->persist($warehouseProductEntity);
                }
            }

            if ($persist) {
                $this->entityManager->flush();
            }

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
