<?php

namespace App\Warehouse\Command;

use App\Shared\Bus\Command\KpyCommandBus;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CommandBus extends KpyCommandBus
{
    public function __construct(#[AutowireIterator('kpy.warehouse.command')] iterable $commands)
    {
        parent::__construct($commands);
    }
}
