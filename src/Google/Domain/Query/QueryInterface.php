<?php

namespace App\Google\Domain\Query;

use App\Shared\Bus\Query\KpyQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.google.query')]
interface QueryInterface extends KpyQueryInterface
{

}
