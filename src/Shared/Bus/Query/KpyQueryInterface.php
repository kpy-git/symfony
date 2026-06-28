<?php

namespace App\Shared\Bus\Query;

interface KpyQueryInterface
{
    public function getName(): string;

    public function fetch(array $params = []): mixed;
}
