<?php

namespace App\Warehouse\Query;

use App\Shared\Bus\Query\KpyQueryBus;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class QueryBus extends KpyQueryBus
{
    public function __construct(#[AutowireIterator('kpy.warehouse.query')] iterable $queries)
    {
        parent::__construct($queries);
    }
}
