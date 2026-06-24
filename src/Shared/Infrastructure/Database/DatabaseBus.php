<?php

namespace App\Shared\Infrastructure\Database;


use App\Shared\Infrastructure\Database\Decorator\DatabaseLoggerDecorator;
use App\Shared\Infrastructure\Database\Exception\KpyNotFoundDatabaseException;
use App\Shared\Infrastructure\Database\Factory\DatabaseFactoryInterface;
use App\Shared\Infrastructure\Database\Trait\DSNParser;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class DatabaseBus implements LoggerAwareInterface
{
    use DSNParser;

    private LoggerInterface $logger;

    /** @var DatabaseFactoryInterface[] $factories */
    public function __construct(
        private readonly iterable $factories)
    {
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getDatabaseBy(array $context): DatabaseInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->isActive() && $factory->supports($context)) {
                $database = $factory->create();

                if (strtolower($_ENV['LOG_DATABASE_QUERIES']) === 'disabled') {
                    return $database;
                }

                return new DatabaseLoggerDecorator(
                    $database,
                    $this->logger,
                    $this->getDatabaseNameFrom($factory->getDSN()),
                    $factory->getDatabaseType()
                );
            }
        }

        throw new KpyNotFoundDatabaseException('No se ha encontrado ninguna base de datos soportada');
    }

    public function getAllActiveDatabases(): array
    {
        $factories = [...$this->factories];
        return array_reduce($factories, function (array $databases, DatabaseFactoryInterface $factory) {
            if ($factory->isActive()) {
                $databases[$this->getDatabaseNameFrom($factory->getDSN())] = $factory->create();
            }
            return $databases;
        }, []);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getAquaDatabase(): DatabaseInterface
    {
        return $this->getDatabaseBy(['name' => 'aqua']);
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getKompyDatabase(): DatabaseInterface
    {
        return $this->getDatabaseBy(['name' => 'kompy']);
    }

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getDoctrineDatabase(): DatabaseInterface
    {
        return $this->getDatabaseBy(['name' => 'doctrine']);
    }
}
