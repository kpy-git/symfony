<?php

namespace App\Database;


use App\Database\Factory\DatabaseFactoryInterface;
use App\Exception\KpyNotFoundDatabaseException;

readonly class DatabaseBus
{
    /** @var DatabaseFactoryInterface[] $factories */
    public function __construct(
        private iterable $factories)
    {
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getDatabaseBy(array $context): DatabaseInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->isActive() && $factory->supports($context)) {
                return $factory->create();
            }
        }

        throw new KpyNotFoundDatabaseException('No se ha encontrado ninguna base de datos soportada');
    }
}
