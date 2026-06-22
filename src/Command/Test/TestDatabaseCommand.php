<?php

namespace App\Command\Test;

use App\Database\DatabaseBus;
use App\Database\Exception\KpyNotFoundDatabaseException;
use App\Database\Exception\KpySqlException;
use PDOException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


readonly class TestDatabaseCommand
{
    public function __construct(private DatabaseBus $databaseBus)
    {
    }

    #[AsCommand(name: 'kpy:test:database', description: 'Test database connection')]
    public function testDatabaseConnection(
        #[Argument] string $databaseName,
        InputInterface     $input,
        OutputInterface    $output
    ): int
    {
        $io = new SymfonyStyle($input, $output);
        $database = null;

        try {
            $database = $this->databaseBus->getDatabaseBy(['name' => $databaseName]);

            $io->success([
                'Conexión realizada correctamente',
                $database->getValue($this->getSqlTestByDatabase($databaseName))
            ]);

            return Command::SUCCESS;

        } catch (KpyNotFoundDatabaseException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;

        } catch (PDOException $exception) {
            $kpySqlException = new KpySqlException(
                $exception->getMessage(),
                __METHOD__,
                $database->getLastSql(),
                $database->getSqlError()
            );
            $io->error($kpySqlException);
            return Command::FAILURE;
        }
    }

    private function getSqlTestByDatabase(string $database): string
    {
        return match ($database) {
            'aqua', 'PIENSOSDATA' => "SELECT TOP 1 RTRIM(NUMERO_DOC) AS PEDIDO FROM DATOP01 WITH(NOLOCK) WHERE TIPOOPER='C' ORDER BY NUMERO DESC",
            'kompy', 'kompy_db', 'kompydev' => 'SELECT id_order FROM ps_orders ORDER BY id_order DESC LIMIT 1',
            default => 'SELECT NOW()'
        };
    }

    #[AsCommand(name: 'kpy:test:databases', description: 'Test all databases connection')]
    public function testAllDatabasesConnection(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->databaseBus->getAllActiveDatabases() as $databaseName => $database) {
            try {
                $io->success([
                    $databaseName . ': conexión realizada correctamente',
                    $database->getValue($this->getSqlTestByDatabase($databaseName))
                ]);

            } catch (PDOException $exception) {
                $kpySqlException = new KpySqlException(
                    $exception->getMessage(),
                    __METHOD__,
                    $database->getLastSql(),
                    $database->getSqlError()
                );
                $io->error($kpySqlException);
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
