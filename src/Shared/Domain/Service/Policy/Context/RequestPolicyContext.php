<?php

namespace App\Shared\Domain\Service\Policy\Context;

use Symfony\Component\HttpFoundation\Request;

final class RequestPolicyContext
{
    public function __construct(
        public Request $request,
        public array $options
    )
    {
    }
}
