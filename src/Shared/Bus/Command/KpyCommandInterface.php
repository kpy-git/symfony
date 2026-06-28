<?php

namespace App\Shared\Bus\Command;

interface KpyCommandInterface
{
    public function getName(): string;

    public function execute(array $params = []): mixed;
}
