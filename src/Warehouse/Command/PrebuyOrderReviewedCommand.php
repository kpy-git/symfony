<?php

namespace App\Warehouse\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class PrebuyOrderReviewedCommand implements CommandInterface
{

    public function __construct(private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.warehouse.command.prebuy_order_reviewed';
    }

    public function execute(array $params = []): bool
    {
        $this->aquaDatabase->execute("DELETE FROM DATKPYORDERPENDSTOCK03 WHERE PEDIDO='{$params['order']}'");

        return true;

    }
}
