<?php

namespace App\Connectif\Infrastructure\ConsoleCommand;

use App\Connectif\Service\PurchaseSynchronizer;
use App\Shared\Domain\Exception\KpyException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class PurchasesSynchronizerConsoleCommand
{
    public function __construct(
        private PurchaseSynchronizer $synchronizer
    )
    {
    }

    #[AsCommand(name: 'kpy:connectif:sync-purchase')]
    public function syncPurchase(
        #[Argument] int $orderId,
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->synchronizer->syncPurchase($orderId);
            $io->success('Venta sincronizada correctamente');

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }

    #[AsCommand(name: 'kpy:connectif:sync-batch-purchases')]
    public function syncPurchasesFrom(
        #[Argument] int $firstOrder,
        InputInterface  $input,
        OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $results = $this->synchronizer->syncPurchasesFrom($firstOrder);

            $io->success("Ventas sincronizadas: " . $results['success']);
            $io->writeln("Ventas no sincronizadas: " . $results['error']);

            $io->writeln('');
            $io->writeln('Último pedido: ' . $results['last_order']);

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
