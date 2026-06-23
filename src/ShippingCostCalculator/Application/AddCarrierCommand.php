<?php

namespace App\ShippingCostCalculator\Application;

use App\ShippingCostCalculator\Domain\Carrier;
use App\ShippingCostCalculator\Domain\Repository\CarrierRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\MapInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddCarrierCommand
{
    #[AsCommand(name: 'kpy:carrier:add', description: 'Add a new carrier')]
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        CarrierRepositoryInterface $carrierRepository,
        #[MapInput] CarrierUserInput $carrierUserInput
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Add Carrier');

        $carrier = new Carrier(
            0,
            $carrierUserInput->name,
            $carrierUserInput->idServiceAqua,
            $carrierUserInput->maxShippingWeight,
            $carrierUserInput->multiparcelAllowed,
            $carrierUserInput->maxParcelWeight
        );

        $carrierRepository->add($carrier);

        $io->success('Carrier created!');

        return Command::SUCCESS;
    }
}
