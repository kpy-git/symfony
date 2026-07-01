<?php

namespace App\Connectif\Query;

use App\Shared\Bus\Query\KpyQueryBus;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ConnectifQueryBus extends KpyQueryBus
{
    public function __construct(#[AutowireIterator('kpy.connectif.query')] iterable $queries)
    {
        parent::__construct($queries);
    }
}
