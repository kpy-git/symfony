<?php

namespace App\Connectif\Infrastructure\ConsoleCommand;

use App\Connectif\Service\ProductSynchronizer;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\ValueObject\ProductCode;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ProductSynchronizerConsoleCommand
{
    public function __construct(private ProductSynchronizer $synchronizer)
    {
    }

    #[AsCommand(name: 'kpy:connectif:sync-product')]
    public function syncProduct(
        #[Argument('SKU del producto')] string $sku,
        InputInterface $input,
        OutputInterface $output,

    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {

            $this->synchronizer->syncProductByCode(ProductCode::fromSKU($sku));

            return Command::SUCCESS;

        } catch (KpyException $kpyException) {
            $io->error($kpyException->getMessage());
            return Command::FAILURE;
        }
    }

    #[AsCommand(name: 'kpy:connectif:sync-all-products')]
    public function syncAllProducts(
        InputInterface $input,
        OutputInterface $output,
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->synchronizer->syncAllProducts();

            return Command::SUCCESS;

        } catch (KpyException $kpyException) {
            $io->error($kpyException->getMessage());
            return Command::FAILURE;
        }
    }
}
