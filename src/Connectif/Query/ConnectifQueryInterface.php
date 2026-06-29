<?php

namespace App\Connectif\Query;

use App\Shared\Bus\Query\KpyQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.connectif.query')]
interface ConnectifQueryInterface extends KpyQueryInterface
{

}
