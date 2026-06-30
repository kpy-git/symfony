<?php

namespace App\Connectif\Command;

use App\Shared\Bus\Command\KpyCommandInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.connectif.command')]
interface ConnectifCommandInterface extends KpyCommandInterface
{

}
