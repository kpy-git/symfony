<?php

namespace App\Connectif\Command;

use App\Shared\Bus\Command\KpyCommandBus;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ConnectifCommandBus extends KpyCommandBus
{
    public function __construct(#[AutowireIterator('kpy.connectif.command')] iterable $commands)
    {
        parent::__construct($commands);
    }
}
