<?php

namespace App\Warehouse\Domain\Carrier\MRW;

use App\Warehouse\Domain\Carrier\AbstractRecipient;

class MRWRecipient extends AbstractRecipient
{

    public function normalize(): array
    {
        return get_object_vars($this);
    }
}
