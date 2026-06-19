<?php

namespace App\Database;


use App\Database\Factory\DatabaseFactoryInterface;
use App\Exception\KpyNotFoundDatabaseException;

class DatabaseBus
{
    /** @var DatabaseFactoryInterface[] $factories */
    public function __construct(
        private iterable $factories)
    {
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getDatabaseBy(array $context): Database
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($context)) {
                return $factory->create();
            }
        }

        throw new KpyNotFoundDatabaseException('No se ha encontrado ninguna base de datos soportada');
    }
}
