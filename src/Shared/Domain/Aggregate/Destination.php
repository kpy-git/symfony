<?php

namespace App\Shared\Domain\Aggregate;

enum Destination: string
{
    case PENINSULA = 'PENINSULA';

    case BALEARES = 'BALEARES';

    case CORDOBA = 'CORDOBA';

    case PORTUGAL = 'PORTUGAL';
}
