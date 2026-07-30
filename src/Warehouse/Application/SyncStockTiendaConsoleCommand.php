<?php

namespace App\Warehouse\Application;

use App\Shared\Bus\Command\KpyCommandNotFoundException;
use App\Shared\Bus\Query\KpyQueryBus;
use App\Shared\Bus\Query\KpyQueryNotFoundException;
use App\Shared\Domain\Exception\KpyInvalidProductCode;
use App\Shared\Domain\ValueObject\ProductCode;
use App\Warehouse\Command\CommandBus;
use App\Warehouse\Query\QueryBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncStockTiendaConsoleCommand
{
    private int $updatedProductsCount;

    public function __construct(
        private QueryBus    $queryBus,
        private KpyQueryBus $kpyQueryBus,
        private CommandBus  $commandBus,
    )
    {
        $this->updatedProductsCount = 0;
    }

    #[AsCommand('kpy:warehouse:sync:stock-tienda')]
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $stockTienda = array_reduce(
            $this->queryBus->fetch('kpy.warehouse.query.stock_kompy_chinales'),
            static function(array $carry, array $row): array {
                $carry[$row['SKU']] = (int)$row['STOCK'];
                return $carry;
            }, []);

        $skuInNeftys = array_map(
            static fn(array $row): string => $row['sku'],
            $this->queryBus->fetch('kpy.warehouse.query.stock_neftys')
        );

        $duplicated = 0;

        foreach ($stockTienda as $sku => $stock) {
            try {
                $productCode = ProductCode::fromSKU($sku);

                if (in_array($sku, $skuInNeftys)) {
                    $duplicated++;
                    continue;
                }

                $this->updateStock($productCode, $stock);

            } catch (KpyInvalidProductCode $e) {
                // los productos KC se os traga por aquí
                continue;
            }
        }

        // productos que solo están en stock en PrestaShop para comprobar si todavía están en stock en tienda
        // cuando un producto se vende en la tienda y se queda a 0 ya no se sincronizaría nunca y se quedaría mal en PS
        $stockPrestaShopOnly = array_map(
            static fn(array $row): ProductCode => ProductCode::from($row['id_product'], $row['id_product_attribute']),
            $this->queryBus->fetch('kpy.warehouse.query.stock_prestashop_only')
        );

        $updatedOutstockProducts = 0;

        foreach ($stockPrestaShopOnly as $product) {
            if (isset($stockTienda[$product->getSku()])) {
                continue;
            }

            $this->updateStock($product, 0);
            $updatedOutstockProducts++;
        }

        $io = new SymfonyStyle($input, $output);

        $io->success($this->updatedProductsCount . ' products(s) updated. ' . $updatedOutstockProducts . ' out of stock in TIENDA.');

        $io->info($duplicated . ' product(s) duplicated with Neftys');

        return Command::SUCCESS;
    }

    /**
     * @throws KpyCommandNotFoundException
     * @throws KpyInvalidProductCode
     * @throws KpyQueryNotFoundException
     */
    private function updateStock(ProductCode $productCode, int $quantity): void
    {
        $this->commandBus->execute('kpy.warehouse.command.update_prestashop_stock', [
            'product_code' => $productCode,
            'quantity' => $quantity,
        ]);

        $this->updatedProductsCount++;

        $packs = $this->kpyQueryBus->fetch('kpy.shared.query.monopack_within_product', [
            'product_code' => $productCode,
        ]);

        if (!empty($packs)) {
            foreach ($packs as $pack) {
                $this->commandBus->execute('kpy.warehouse.command.update_prestashop_stock', [
                    'product_code' => ProductCode::fromSKU($pack['id_product_pack']),
                    'quantity' => floor($quantity / $pack['quantity']),
                ]);

                $this->updatedProductsCount++;
            }
        }
    }

}
