<?php

namespace App\Warehouse\Domain\Carrier\MRW;

class GetEtiquetaEnvioResultDTO
{
    public int $Estado;
    public string $Mensaje;
    public string $EtiquetaFileZpl;
}
