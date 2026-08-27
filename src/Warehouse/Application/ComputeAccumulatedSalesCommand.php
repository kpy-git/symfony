<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Query\QueryBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ComputeAccumulatedSalesCommand
{
    public function __construct(
        private QueryBus $queryBus,
        private CommandBus $commandBus,
    )
    {
    }

    #[AsCommand('kpy:warehouse:compute-accumulated-sales', description: 'Calcula las ventas acumuladas de los últimos 30 y 90 días')]
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $sales30d = $this->queryBus->fetch('kpy.warehouse.query.accumulated_sales', [
                'filter' => '30d',
                'group_by' => 'warehouse',
            ]);

            $sales90d = $this->queryBus->fetch('kpy.warehouse.query.accumulated_sales', [
                'filter' => '90d',
                'group_by' => 'warehouse',
            ]);

            $accumulatedSalesIndex = [];
            foreach ($sales30d as $sale30) {
                $accumulatedSalesIndex[$sale30['CODIGO']][$sale30['ALMACEN']] = [
                    'VENTAS_30' => (int)$sale30['VENTAS'],
                    'VENTAS_90' => 0,
                ];
            }

            foreach ($sales90d as $sale90) {
                if (isset($accumulatedSalesIndex[$sale90['CODIGO']][$sale90['ALMACEN']])) {
                    $accumulatedSalesIndex[$sale90['CODIGO']][$sale90['ALMACEN']]['VENTAS_90'] = (int)$sale90['VENTAS'];

                } else {
                    $accumulatedSalesIndex[$sale90['CODIGO']][$sale90['ALMACEN']] = [
                        'VENTAS_30' => 0,
                        'VENTAS_90' => (int)$sale90['VENTAS'],
                    ];
                }
            }
            $accumulatedSalesByWarehouse = [];
            $accumulatedSales = [];
            foreach ($accumulatedSalesIndex as $sku => $warehouse) {
                $accumulatedSalesByProduct = [
                    '30d' => 0,
                    '90d' => 0,
                ];

                foreach ($warehouse as $warehouseName => $sales) {
                    $accumulatedSalesByWarehouse[] = [
                        'CODIGO' => $sku,
                        'ALMACEN' => $warehouseName,
                        'VENTAS_30' => $sales['VENTAS_30'],
                        'VENTAS_90' => $sales['VENTAS_90'],
                    ];

                    $accumulatedSalesByProduct['30d'] += $sales['VENTAS_30'];
                    $accumulatedSalesByProduct['90d'] += $sales['VENTAS_90'];
                }

                $accumulatedSales[] = [
                    'CODIGO' => $sku,
                    'VENTAS_30' => $accumulatedSalesByProduct['30d'],
                    'VENTAS_90' => $accumulatedSalesByProduct['90d'],
                ];
            }

            $this->commandBus->execute('kpy.warehouse.command.update_accumulated_sales_by_warehouse', [
                'accumulated_sales' => $accumulatedSalesByWarehouse,
            ]);

            $this->commandBus->execute('kpy.warehouse.command.update_accumulated_sales', [
                'accumulated_sales' => $accumulatedSales,
            ]);

            $io->success(count($accumulatedSales) . " productos actualizados correctamente");

            return Command::SUCCESS;

        } catch (KpyException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
