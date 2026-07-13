<?php

namespace App\Warehouse\Domain\CostStrategy;

enum CostStrategyType: string
{
    case NEFTYS = 'NEFTYS';

    case OWNERSHIP = 'OWNERSHIP';
}
