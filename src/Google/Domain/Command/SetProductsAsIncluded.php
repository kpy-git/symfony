<?php

namespace App\Google\Domain\Command;

use App\Shared\Infrastructure\Database\DatabaseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class SetProductsAsIncluded implements CommandInterface
{

    public function __construct(#[Autowire(service: 'aquaDatabase')] private DatabaseInterface $aquaDatabase)
    {
    }

    public function getName(): string
    {
        return 'kpy.command.google.set_products_as_included';
    }

    public function execute(array $params = []): mixed
    {
        if (!isset($params['skus']) || !is_array($params['skus'])) {
            throw new \InvalidArgumentException('"skus" must be an array');
        }

        return $this->aquaDatabase->execute(
            "UPDATE DATPYMPRDPRICES01 SET GSHOPINGESP=1 WHERE PRODUCTO IN ('" . implode("', '", $params['skus']) . "')"
        );
    }
}
