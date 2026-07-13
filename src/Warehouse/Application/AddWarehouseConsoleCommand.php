<?php

namespace App\Warehouse\Application;

use App\Warehouse\Domain\CostStrategy\CostStrategyType;
use App\Warehouse\Domain\Exception\WarehouseException;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;

readonly class AddWarehouseConsoleCommand
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws WarehouseException
     */
    #[AsCommand('kpy:warehouse:add')]
    public function __invoke(
        InputInterface   $input,
        OutputInterface  $output,
        #[Argument]
        #[Ask('Nombre del almacén', constraints: [new Assert\NotBlank()])]
        string           $name,
        #[Argument]
        #[Ask('ID servicio de transporte')]
        string           $service,
        #[Argument]
        #[Ask('Tipo de estrategía para el cálculo de costes')]
        CostStrategyType $costStrategyType,
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->entityManager->getRepository(Warehouse::class)->findOneBy(['name' => mb_strtoupper($name)])) {
            $io->error('Ya existe un almacén con el nombre \'' . mb_strtoupper($name) . '\'');
            return Command::FAILURE;
        }

        $warehouse = new Warehouse();
        $warehouse
            ->setName($name)
            ->setCarrierService($service)
            ->setCostStrategyType($costStrategyType);

        $this->entityManager->persist($warehouse);
        $this->entityManager->flush();

        $io->success('Almacén agregado correctamente');

        return Command::SUCCESS;
    }
}
