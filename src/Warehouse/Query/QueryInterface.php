<?php

namespace App\Warehouse\Query;

use App\Shared\Bus\Query\KpyQueryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kpy.warehouse.query')]
interface QueryInterface extends KpyQueryInterface
{

}
