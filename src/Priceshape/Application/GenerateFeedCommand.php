<?php

namespace App\Priceshape\Application;

use App\Priceshape\Domain\ProductProvider;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Shop;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

readonly class GenerateFeedCommand
{
    public function __construct(
        private ProductProvider $productProvider
    )
    {
    }

    #[AsCommand('kpy:priceshape:generate:feed')]
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        Filesystem $filesystem,
        #[Autowire('%kpy.priceshape.var_dir%')] string $varDir,
        #[Autowire('%kernel.environment%')] string $environment,
        #[Argument] Shop $shop = Shop::KOMPY_ES,
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $products = $this->productProvider->getProductsByShop($shop);

            if (empty($products)) {
                $io->warning('No se ha encontrado ningún producto para incluir en el feed');
                return Command::SUCCESS;
            }

            $io->success('Feed generado correctamente, ' . count($products) . ' productos');
            $filesystem->dumpFile($varDir . '/' . $shop->value . '.json', json_encode($products, JSON_PRETTY_PRINT));

            if ($environment === 'dev') {
                $io->warning(['Productos excluidos:', "\n", implode('|', $this->productProvider->getProductsExcluded())]);
            }

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
