<?php

namespace App\Shared\Domain\Service\Policy\Contract;

interface KpyPolicyInterface
{
    public function apply(object $context): void;
}
