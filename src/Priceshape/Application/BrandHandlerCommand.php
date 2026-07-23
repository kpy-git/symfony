<?php

namespace App\Priceshape\Application;

use App\Priceshape\Infrastructure\Persistence\Doctrine\Model\BrandIncluded;
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

    #[AsCommand('kpy:priceshape:brand:add')]
    public function addBrand(
        #[Argument] int $id_manufacturer,
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->em->getRepository(BrandIncluded::class)->find($id_manufacturer)) {
            $io->warning('La marca ya se estaba incluída');
            return Command::SUCCESS;
        }

        $this->em->persist(new BrandIncluded($id_manufacturer));
        $this->em->flush();

        $io->success('La marca se ha incluído correctamente en el feed de Priceshape');
        return Command::SUCCESS;
    }

    #[AsCommand('kpy:priceshape:brand:remove')]
    public function removeBrand(
        #[Argument] int $id_manufacturer,
        InputInterface  $input,
        OutputInterface $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        $brand = $this->em->getRepository(BrandIncluded::class)->find($id_manufacturer);

        if (!$brand) {
            $io->warning('La marca no está incluída en el feed de Priceshape');
            return Command::SUCCESS;
        }

        $this->em->remove($brand);
        $this->em->flush();

        $io->success('La marca se ha eliminado correctamente del feed de Priceshape');
        return Command::SUCCESS;
    }

    public function addFixedPrice(
        #[Argument] int $brandId,
    ): int
    {
        return Command::SUCCESS;
    }
}
