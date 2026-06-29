<?php

namespace App\Google\Domain\Command;

use App\Shared\Bus\Command\KpyCommandInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.google.command')]
interface CommandInterface extends KpyCommandInterface
{

}
