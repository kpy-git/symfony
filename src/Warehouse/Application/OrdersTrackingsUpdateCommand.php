<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\OrderTrackerUpdater;
use App\Warehouse\Domain\ValueObject\TrackingState;
use App\Warehouse\Domain\ValueObject\TrackingUpdateResult;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class OrdersTrackingsUpdateCommand
{
    public function __construct(private OrderTrackerUpdater $orderTrackerUpdater)
    {
    }

    #[AsCommand('kpy:warehouse:tracking:update-order', description: 'Update order tracking')]
    public function updateOrderHistory(
        InputInterface  $input,
        OutputInterface $output,
        #[Argument] int $orderId
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->orderTrackerUpdater->updateHistoryByOrder($orderId);

            if (!$result->withStatusChanges()) {
                $io->info([
                    "Pedido sin cambios",
                    sprintf("Último estado: %d [%s]", $result->getLastStateId(), $result->getLastUpdate()->format('d/m/Y H:i:s')),
                ]);
                return Command::SUCCESS;
            }

            $io->success("Pedido actualizado");
            $io->writeln(sprintf("Último estado: %d [%s]", $result->getLastStateId(), $result->getLastUpdate()->format('d/m/Y H:i:s')));

            /** @var TrackingState $state */
            foreach ($result->getHistory() as $state) {
                $io->writeln((string)$state);
            }

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }

    #[AsCommand('kpy:warehouse:tracking:update-undelivered-orders', description: 'Update all undelivered orders tracking')]
    public function updateTrackingUndeliveredOrders(
        InputInterface $input,
        OutputInterface $output,
        #[Option(description: 'Muestra todos los cambios de estado encontrados')]
        bool $details = false,
        #[Option(description: 'Límite de pedidos para actualizar')]
        int $limit = 0
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->orderTrackerUpdater->updateHistoryUndeliveredOrders($limit);

            $io->success([
                "Pedidos pendientes de entregar: " . $result['undelivered_orders'],
                "Pedidos actualizados: " . $result['updated_orders'],
            ]);

            if ($result['updated_orders'] > 0 && $details) {
                /** @var TrackingUpdateResult $history */
                foreach ($result['results'] as $history) {
                    $io->info([
                        "Pedido " . $history->getOrderId(),
                        sprintf("último estado: %d [%s]", $history->getLastStateId(), $history->getLastUpdate()->format('d/m/Y H:i:s')),
                    ]);
                    $io->writeln(array_map(static fn (TrackingState $result): string => (string)$result, $history->getHistory()));
                }
            }

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
