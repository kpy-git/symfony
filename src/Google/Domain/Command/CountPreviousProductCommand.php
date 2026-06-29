<?php

namespace App\Google\Domain\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class CountPreviousProductCommand implements CommandInterface
{
    public function __construct(#[Autowire(service: 'aquaDatabase')] private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.command.google.count_previous_products';
    }

    public function execute(array $params = []): int
    {
        return (int)$this->aquaDatabase->getValue(
            "SELECT COUNT(*) FROM DATPYMPRDPRICES01 WITH(NOLOCK) WHERE GSHOPINGESP=1"
        );
    }
}
