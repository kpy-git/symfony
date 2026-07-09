<?php

namespace App\Warehouse\Domain\Application;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

class AddPackagingConsoleCommand
{
    #[AsCommand("kpy:warehouse:packaging:add")]
    public function addPackaging(): int
    {
        return Command::SUCCESS;
    }
}
