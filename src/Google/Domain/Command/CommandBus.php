<?php

namespace App\Google\Domain\Command;

use App\Shared\Bus\Command\KpyCommandBus;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CommandBus extends KpyCommandBus
{
    public function __construct(
        #[AutowireIterator('kpy.google.command')]
        iterable $commands
    )
    {
        parent::__construct($commands);
    }
}
