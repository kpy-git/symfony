<?php

namespace App\Priceshape\Application;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Model\BrandBanned;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class BrandHandlerCommand
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[AsCommand('kpy:priceshape:brand:banned')]
    public function addBanned(
        #[Argument] int $brandId,
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->em->getRepository(BrandBanned::class)->find($brandId)) {
            $io->warning('La marca ya se encuentra excluída');
            return Command::SUCCESS;
        }

        $this->em->persist(new BrandBanned($brandId));
        $this->em->flush();

        $io->success('La marca se ha excluído correctamente de Priceshape');
        return Command::SUCCESS;
    }

    public function addFixedPrice(
        #[Argument] int $brandId,
    ): int
    {
        return Command::SUCCESS;
    }
}
