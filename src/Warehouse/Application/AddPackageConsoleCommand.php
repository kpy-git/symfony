<?php

namespace App\Warehouse\Application;

use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\PackageModel;
use App\Warehouse\Infrastructure\Persistence\Doctrine\Model\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;

class AddPackageConsoleCommand
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[AsCommand("kpy:warehouse:package:add")]
    public function addPackaging(
        InputInterface $input,
        OutputInterface $output,
    ): int
    {
        $helper = new QuestionHelper();

        $output->writeln('Nombre del almacén [string]:');
        $questionWarehouse = new Question("> ");
        $questionWarehouse->setValidator(function (string $answer): Warehouse {
            $warehouse = $this->entityManager->getRepository(Warehouse::class)->findOneBy(['name' => mb_strtoupper($answer)]);
            if (null === $warehouse) {
                throw new \RuntimeException('No existe ningún almacén con el nombre \'' . $answer . '\'');
            }
            return $warehouse;
        });

        $warehouse = $helper->ask($input, $output, $questionWarehouse);

        $output->writeln('');

        $repeat = true;

        while ($repeat) {
            $package = new PackageModel();
            $package->setWarehouse($warehouse);

            $output->writeln('Nombre de la caja [string]:');
            $questionPackageName = new Question('> ');
            $questionPackageName->setConstraints([new Assert\NotBlank()]);
            $package->setName($helper->ask($input, $output, $questionPackageName));

            $output->writeln('Coste de la caja [decimal]:');
            $questionCost = new Question('> ');
            $questionCost->setConstraints([
                new Assert\NotBlank(),
                new Assert\PositiveOrZero(),
            ]);
            $package->setCost($helper->ask($input, $output, $questionCost));

            $output->writeln('Peso máximo admitido por la caja [decimal]:');
            $questionMaxWeight = new Question('> ');
            $questionMaxWeight->setConstraints([
                new Assert\NotBlank(),
                new Assert\Positive(),
            ]);
            $package->setMaxWeightAllowed($helper->ask($input, $output, $questionMaxWeight));

            $output->writeln('Peso de la caja (presiona <return> si lo desconoces) [decimal]:');
            $questionWeight = new Question('> ', 0);
            $questionWeight->setConstraints([
                new Assert\NotBlank(),
                new Assert\PositiveOrZero(),
            ]);
            $package->setWeight($helper->ask($input, $output, $questionWeight));

            $this->entityManager->persist($package);

            $output->writeln('¿Quieres añadir más cajas? (yes/no) [no]');
            $repeatQuestion = new ConfirmationQuestion('> ', false);
            $repeat = $helper->ask($input, $output, $repeatQuestion);
        }

        $this->entityManager->flush();

        new SymfonyStyle($input, $output)->success('Cajas agregadas correctamente');

        return Command::SUCCESS;
    }
}
