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

    private array $pool = [];

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
        /** @var DatabaseFactoryInterface $factory */
        foreach ($this->factories as $factory) {
            if ($factory->isActive() && $factory->supports($context)) {
                return $this->getDatabaseInstance($factory);
            }
        }

        throw new KpyNotFoundDatabaseException('No se ha encontrado ninguna base de datos soportada');
    }

    private function getDatabaseInstance(DatabaseFactoryInterface $factory): DatabaseInterface
    {
        if (isset($this->pool[$this->getDatabaseNameFrom($factory->getDSN())])) {
            return $this->pool[$this->getDatabaseNameFrom($factory->getDSN())];
        }

        $this->pool[$this->getDatabaseNameFrom($factory->getDSN())] = $this->addLoggerDecoratorIsEnabled($factory);

        return $this->pool[$this->getDatabaseNameFrom($factory->getDSN())];
    }

    private function addLoggerDecoratorIsEnabled(DatabaseFactoryInterface $factory): DatabaseInterface
    {
        if (strtolower($_ENV['LOG_DATABASE_QUERIES']) === 'disabled') {
            return $factory->create();
        }

        return new DatabaseLoggerDecorator(
            $factory->create(),
            $this->logger,
            $this->getDatabaseNameFrom($factory->getDSN()),
            $factory->getDatabaseType()
        );
    }

    public function getAllActiveDatabases(): array
    {
        $factories = [...$this->factories];
        return array_reduce($factories, function (array $databases, DatabaseFactoryInterface $factory) {
            if ($factory->isActive()) {
                $databases[$this->getDatabaseNameFrom($factory->getDSN())] = $this->getDatabaseInstance($factory);
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

    /**
     * @throws KpyNotFoundDatabaseException
     */
    public function getPymLegacyDatabase(): DatabaseInterface
    {
        return $this->getDatabaseBy(['name' => 'pym']);
    }
}
