<?php

namespace App\Shared\Domain\Service\Policy;

use App\Shared\Domain\Service\Policy\Contract\KpyPolicyInterface;

abstract class RequestPolicy implements KpyPolicyInterface
{
    public const string MANDATORY_PARAMS = 'mandatory_params';

    public abstract function apply(object $context): void;
}
