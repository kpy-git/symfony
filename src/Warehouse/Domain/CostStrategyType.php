<?php

namespace App\Warehouse\Domain;

enum CostStrategyType: string
{
    case FIXED_PERCENT = 'FIXED_PERCENT';

    case FIXED_AMOUNT = 'FIXED_AMOUNT';

    case OWNERSHIP = 'OWNERSHIP';
}
