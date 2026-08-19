<?php

namespace App\Warehouse\Application;

use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Service\OrderReadyToShipUpdater;
use App\Warehouse\Domain\ValueObject\ExchangeMail;
use App\Warehouse\Service\ExchangeMailboxHandler;
use App\Warehouse\Service\MicrosoftGraphAuth;
use App\Warehouse\Service\NeftysMailParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('kpy:warehouse:neftys-orders-notifications-parser')]
class ParserNeftysOrderNotifications
{
    private string $mailbox = 'integraciones@kompymascotas.com';

    public function __invoke(
        MicrosoftGraphAuth      $microsoftGraphAuth,
        ExchangeMailboxHandler  $exchangeMailboxReader,
        NeftysMailParser        $neftysMailParser,
        OrderReadyToShipUpdater $orderReadyToShipUpdater,
        InputInterface          $input,
        OutputInterface         $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $accessToken = $microsoftGraphAuth->getAccessToken();

            $exchangeMailboxReader->setToken($accessToken);

            $emails = $exchangeMailboxReader->getUnreadMails(
                $this->mailbox,
                'AAMkAGJjNjI3MWZkLTg3ZDAtNDI4ZC04MmRjLWE1MjExOTUwNGM2NQAuAAAAAACHjNKcrBEXQIXGfa1KCTQwAQC1wf2mYLyyRLEwEvVSntgWAAAK8rEEAAA='
            );

            $orderCount = 0;

            /** @var ExchangeMail $email */
            foreach ($emails as $email) {
                $neftysMailParser->parserMail($email);

                $io->write(sprintf("Pedido %d: %s", $neftysMailParser->getOrderId(), $neftysMailParser->getTrackingNumber()));
                $io->newLine();

                if (!$neftysMailParser->getOrderId()) {
                    $io->warning('Pedido no encontrado, [' . $email->getBodyPreview() . ']');
                    continue;
                }

                $orderReadyToShipUpdater->updateOrder($neftysMailParser->getOrderId(), $neftysMailParser->getTrackingNumber());
                $orderCount++;

                $exchangeMailboxReader->markAsRead($this->mailbox, $email->getId());
            }

            $io->success($orderCount . " pedidos actualizados");
            return Command::SUCCESS;

        } catch (KpyException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
