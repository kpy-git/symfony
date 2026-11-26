<?php

namespace App\Shared\Domain\Service;

use App\Shared\Infrastructure\Database\DatabaseInterface;

readonly class AquaCountersHandler
{
    public function __construct(
        private DatabaseInterface $aquaDatabase,
        private string            $aquaCompany,
        private string            $aquaExercise,
    )
    {
    }

    public function getNewOperationNumber(): int
    {
        $this->aquaDatabase->beginTransaction();

        $operacion = $this->aquaDatabase->getValue("SELECT DATINTERNALCOUNTER.NUMERO_OP
                        FROM DATINTERNALCOUNTER WITH (UPDLOCK)
                        WHERE DATINTERNALCOUNTER.COMPANY = '{$this->aquaCompany}' AND DATINTERNALCOUNTER.EXERCISE = '{$this->aquaExercise}'");

        $siguiente = $operacion + 1;

        $this->aquaDatabase->execute("UPDATE DATINTERNALCOUNTER
            SET DATINTERNALCOUNTER.NUMERO_OP = {$siguiente}
            WHERE DATINTERNALCOUNTER.COMPANY = '{$this->aquaCompany}' AND DATINTERNALCOUNTER.EXERCISE = '{$this->aquaExercise}'");

        $this->aquaDatabase->commit();

        return $operacion;
    }

    public function getNewRangeOperationNumber(int $count): array
    {
        $this->aquaDatabase->beginTransaction();

        $operacion = $this->aquaDatabase->getValue("SELECT NUMERO_OP
                        FROM DATINTERNALCOUNTER WITH (UPDLOCK)
                        WHERE COMPANY = '{$this->aquaCompany}' AND EXERCISE = '{$this->aquaExercise}'");

        $siguiente = $operacion + $count;

        $this->aquaDatabase->execute("UPDATE DATINTERNALCOUNTER
            SET DATINTERNALCOUNTER.NUMERO_OP = {$siguiente}
            WHERE COMPANY = '{$this->aquaCompany}' AND EXERCISE = '{$this->aquaExercise}'");

        $this->aquaDatabase->commit();

        return range($operacion, $siguiente - 1);
    }

    public function getNewOrderBuyDocNumber(): string
    {
        $this->aquaDatabase->beginTransaction();

        $infoEmpresa = $this->aquaDatabase->getRow(
            "SELECT SERIE_PPED, PPEDIDO FROM CMNEM WITH(NOLOCK) WHERE NUMERO='{$this->aquaCompany}' AND EJERCICIO='{$this->aquaExercise}'"
        );

        $siguiente = (int)$infoEmpresa['PPEDIDO'] + 1;
        $this->aquaDatabase->execute(
            "UPDATE CMNEM SET PPEDIDO=" . $siguiente . " WHERE NUMERO='{$this->aquaCompany}' AND EJERCICIO='{$this->aquaExercise}'"
        );

        $this->aquaDatabase->commit();

        return $infoEmpresa['SERIE_PPED'] . str_pad($infoEmpresa['PPEDIDO'], 8, '0', STR_PAD_LEFT);
    }
}
