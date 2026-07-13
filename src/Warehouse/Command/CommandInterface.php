<?php

namespace App\Warehouse\Command;

use App\Shared\Bus\Command\KpyCommandInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.command')]
interface CommandInterface extends KpyCommandInterface
{

}
