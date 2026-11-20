<?php

namespace App\Shared\Domain\Service\Policy\Context;

final class InsufficientParamsForQueryContext
{
    public function __construct(
        public array $mandatoryParams,
        public array $queryParams,
    )
    {
    }
}
