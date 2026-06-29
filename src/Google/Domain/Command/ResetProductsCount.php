<?php

namespace App\Google\Domain\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ResetProductsCount implements CommandInterface
{
    public function __construct(#[Autowire(service: 'aquaDatabase')] private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.command.google.reset_products_count';
    }

    public function execute(array $params = []): bool
    {
        return $this->aquaDatabase->execute("UPDATE DATPYMPRDPRICES01 SET GSHOPINGESP=0");
    }
}
