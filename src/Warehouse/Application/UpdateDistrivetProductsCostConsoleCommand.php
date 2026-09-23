<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Shared\Infrastructure\API\KpyPublicApiInterface;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Query\QueryBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class UpdateDistrivetProductsCostConsoleCommand
{
    public function __construct(
        private KpyPublicApiInterface $kpyApi,
        private CommandBus $commandBus,
        private QueryBus $queryBus)
    {
    }

    #[AsCommand('kpy:warehouse:update-distrivet-cost')]
    public function __invoke(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $io = new SymfonyStyle($input, $output);
        $countUpdatedProducts = 0;

        try {
            $productsCosts = $this->queryBus->fetch('kpy.warehouse.query.distrivet_products_cost');

            if (empty($productsCosts)) {
                $io->warning('No hay ningún producto disponible para actualizar');
                return Command::SUCCESS;
            }

            foreach ($productsCosts as $productCost) {
                $productCode = ProductCode::from($productCost['id_product'], $productCost['id_product_attribute']);

                $product = $this->kpyApi->getProduct($productCode);

                if (!$product->isPack()) {
                    $this->commandBus->execute('kpy.warehouse.command.update_aqua_cost_product_price', [
                        'sku' => $productCode->getSku(),
                        'supplier' => '400000001',
                        'cost' => $productCost['cost'],
                    ]);
                }

                if ($product->getBrandId() === 1) {
                    // Hills Rappels (7% y 2%) + 15% en factura
                    $this->commandBus->execute('kpy.warehouse.command.update_final_product_cost', [
                        'id_product' => $productCode->getProductId(),
                        'id_product_attribute' => $productCode->getProductAttributeId(),
                        'final_cost' => round($productCost['cost'] * 0.77469, 6),
                    ]);
                }

                $countUpdatedProducts++;
            }

            $io->success( $countUpdatedProducts . ' productos actualizados satisfactoriamente');

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
