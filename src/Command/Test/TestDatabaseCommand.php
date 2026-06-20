<?php

namespace App\Command\Test;

use App\Database\DatabaseBus;
use App\Exception\KpyNotFoundDatabaseException;
use App\Exception\KpySqlException;
use PDOException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kpy:test:database', description: 'Test database connection')]
class TestDatabaseCommand extends Command
{
    public function __construct(private DatabaseBus $databaseBus, ?string $name = null, ?callable $code = null)
    {
        parent::__construct($name, $code);
    }

    public function __invoke(
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
            'aqua' => "SELECT TOP 1 RTRIM(NUMERO_DOC) AS PEDIDO FRM DATOP01 WITH(NOLOCK) WHERE TIPOOPER='C' ORDER BY NUMERO DESC",
            default => 'SELECT NOW()'
        };
    }
}
