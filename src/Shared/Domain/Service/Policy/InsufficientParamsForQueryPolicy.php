<?php

namespace App\Shared\Domain\Service\Policy;

use App\Shared\Domain\Exception\KpyInsufficientParamsForQueryPolicyException;
use App\Shared\Domain\Service\Policy\Context\InsufficientParamsForQueryContext;
use App\Shared\Domain\Service\Policy\Contract\KpyPolicyInterface;

class InsufficientParamsForQueryPolicy implements KpyPolicyInterface
{
    public function apply(object $context): void
    {
        if (!$context instanceof InsufficientParamsForQueryContext) {
            throw new \InvalidArgumentException('Invalid policy context');
        }

        if (!empty(array_diff($context->mandatoryParams, $context->queryParams))) {
            throw new KpyInsufficientParamsForQueryPolicyException(
                sprintf('Parámetros insuficientes para ejecutar la consulta!: [%s]',
                    implode(', ', $context->mandatoryParams))
            );
        }
    }
}
