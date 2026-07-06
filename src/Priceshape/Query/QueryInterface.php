<?php

namespace App\Priceshape\Query;

use App\Shared\Bus\Query\KpyQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.priceshape.query')]
interface QueryInterface extends KpyQueryInterface
{

}
