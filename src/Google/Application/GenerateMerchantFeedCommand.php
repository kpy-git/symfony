<?php

namespace App\Google\Application;

use App\Google\Service\GoogleMerchantFeedHandler;
use App\Shared\Domain\Exception\KpyException;
use App\Shared\Domain\Shop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class GenerateMerchantFeedCommand
{
    public function __construct(private GoogleMerchantFeedHandler $feedHandler)
    {
    }

    #[AsCommand(name: 'kpy:google:generate-feed', description: 'Generate feed for Google Merchant')]
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->feedHandler->syncFeed(Shop::KOMPY_ES);

            $io->success('Feed generated.');

            return Command::SUCCESS;

        } catch (KpyException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
