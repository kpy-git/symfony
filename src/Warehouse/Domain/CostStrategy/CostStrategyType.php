<?php

namespace App\Warehouse\Domain\CostStrategy;

enum CostStrategyType: string
{
    case NEFTYS = 'NEFTYS';

    case OWNERSHIP = 'OWNERSHIP';

    case EVOLUTION_PETS = 'EVOLUTION_PETS';

    case DISTRIVET = 'DISTRIVET';
}
