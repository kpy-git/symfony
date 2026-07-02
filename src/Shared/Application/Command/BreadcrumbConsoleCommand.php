<?php

namespace App\Shared\Application\Command;

use App\Shared\Domain\Service\CategoriesBreadcrumbGenerator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BreadcrumbConsoleCommand
{
    #[AsCommand(name: 'kpy:test:breadcrumb')]
    public function __invoke(
        #[Argument] int $productId,
        InputInterface $input,
        OutputInterface $output,
        CategoriesBreadcrumbGenerator $categoriesBreadcrumbGenerator
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        $breadcrumb = $categoriesBreadcrumbGenerator->getAllCategoriesBreadcrumbByProduct($productId);

        foreach ($breadcrumb as $category) {
            $io->writeln($category);
        }

        return Command::SUCCESS;
    }
}
